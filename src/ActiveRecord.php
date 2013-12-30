<?php

namespace Core;

abstract class ActiveRecord extends \Ext\Db\ActiveRecord
{
    /**
     * @return string
     */
    public static function computeTable()
    {
        return \Ext\Db::get()->getPrefix() . \Ext\String::underline(str_replace(
            array('App\\Cms\\', 'App\\'),
            '',
            get_called_class()
        ));
    }

    /**
     * @return bool
     */
    public function create()
    {
        if (parent::create()) {

            // Обновление кэша APC

            if (\App\Cms\Cache\Apc::isEnabled()) {
                $key = static::getApcListKey();
                $list = \App\Cms\Cache\Apc::instance()->get($key);

                if ($list !== null) {
                    $list[$this->getApcId()] = $this;
                    \App\Cms\Cache\Apc::instance()->set($key, $list);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function update()
    {
        $result = parent::update();


        // Обновление кэша APC

        if ($result && \App\Cms\Cache\Apc::isEnabled()) {
            $id = $this->getApcId();
            \App\Cms\Cache\Apc::instance()->delete(static::getApcItemKey($id));
            $key = static::getApcListKey();
            $list = \App\Cms\Cache\Apc::instance()->get($key);

            if ($list !== null && !empty($list[$id])) {
                $list[$id] = $this->fetch($id);
                \App\Cms\Cache\Apc::instance()->set($key, $list);
            }
        }

        return $result;
    }

    /**
     * @param string $_name
     * @param string|number $_value
     * @return bool
     */
    public function updateAttr($_name, $_value = null)
    {
        $result = parent::updateAttr($_name, $_value);


        // Обновление кэша APC

        if ($result && \App\Cms\Cache\Apc::isEnabled()) {
            $id = $this->getApcId();
            \App\Cms\Cache\Apc::instance()->delete(static::getApcItemKey($id));
            $key = static::getApcListKey();
            $list = \App\Cms\Cache\Apc::instance()->get($key);

            if ($list !== null && !empty($list[$id])) {
                $list[$id] = $this->fetch($id);
                \App\Cms\Cache\Apc::instance()->set($key, $list);
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $result = parent::delete();


        // Удаление из кэша APC

        if ($result && \App\Cms\Cache\Apc::isEnabled()) {
            $id = $this->getApcId();
            \App\Cms\Cache\Apc::instance()->delete(static::getApcItemKey($id));
            $key = static::getApcListKey();
            $list = \App\Cms\Cache\Apc::instance()->get($key);

            if ($list !== null && !empty($list[$id])) {
                unset($list[$id]);
                \App\Cms\Cache\Apc::instance()->set($key, $list);
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public static function truncate()
    {
        static::clearApcList();

        return (bool) \Ext\Db::get()->execute('TRUNCATE ' . static::getTbl());
    }

    /**
     *
     * Оптимизация обращений к БД с помощью APC.
     *
     */

    public function getApcId()
    {
        $id = $this->getId();
        return is_array($id) ? implode('-', $id) : $id;
    }

    public static function getApcListKey()
    {
        return \Ext\Db::get()->getDatabase() . '-' .
        static::getTbl() . '-' .
        'list';
    }

    public static function getApcItemKey($_id)
    {
        return \Ext\Db::get()->getDatabase() . '-' .
        static::getTbl() . '-' .
        $_id;
    }

    public static function clearApcList()
    {
        if (\App\Cms\Cache\Apc::isEnabled()) {
            \App\Cms\Cache\Apc::instance()->delete(static::getApcListKey());
        }
    }

    public static function getList($_where = null, $_params = array())
    {
        if (
            empty($_where) &&
            empty($_params) &&
            \App\Cms\Cache\Apc::isEnabled() &&
            static::isOptimFetchStrategy()
        ) {
            $key = static::getApcListKey();
            $list = \App\Cms\Cache\Apc::instance()->get($key);

            if ($list === null) {
                $list = static::fetchList();
                \App\Cms\Cache\Apc::instance()->set($key, $list);
            }

            return $list;

        } else {
            return static::fetchList($_where, $_params);
        }
    }

    public static function load($_value, $_attr = null)
    {
        if (
            \App\Cms\Cache\Apc::isEnabled() &&
            !($_attr && is_array($_attr)) &&
            (is_null($_attr) || static::isOptimFetchStrategy())
        ) {
            if (static::isOptimFetchStrategy()) {
                if (is_null($_attr)) {
                    $list = static::getList();
                    return array_key_exists($_value, $list)
                         ? $list[$_value]
                         : false;

                } else {
                    foreach (static::getList() as $item) {
                        if ($item->$_attr == $_value) {
                            return $item;
                        }
                    }

                    return false;
                }

            } else {
                $key = static::getApcItemKey($_value);
                $item = \App\Cms\Cache\Apc::instance()->get($key);

                if ($item === null) {
                    $item = static::fetch($_value, $_attr);
                    \App\Cms\Cache\Apc::instance()->set($key, $item);
                }

                return $item;
            }

        } else {
            return static::fetch($_value, $_attr);
        }
    }

    public static function getInfo()
    {
        $info = null;
        $db = \Ext\Db::get()->getDatabase();

        if (\App\Cms\Cache\Apc::isEnabled()) {
            $key = "$db-tables-info";
            $info = \App\Cms\Cache\Apc::instance()->get($key);
        }

        if (!$info) {
            $list = \Ext\Db::get()->getList("
                SELECT TABLE_NAME, TABLE_ROWS
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = '$db'
            ");

            $info = array();

            foreach ($list as $item) {
                $info[$item['TABLE_NAME']] = $item;
            }

            if (\App\Cms\Cache\Apc::isEnabled()) {
                \App\Cms\Cache\Apc::instance()->set($key, $info);
            }
        }

        return $info;
    }

    public static function getRoughlyAmount()
    {
        $info = static::getInfo();
        $table = static::getTbl();

        return empty($info) || empty($info[$table])
            ? false
            : $info[$table]['TABLE_ROWS'];
    }

    public static function isOptimFetchStrategy()
    {
        return static::getRoughlyAmount() < 100;
    }
}
