<?php

abstract class Core_ActiveRecord
{
    /**
     * @var string
     */
    protected $_table;

    /**
     * @var array[App_ActiveRecord_Attribute]
     */
    protected $_attributes;

    /**
     * @var array[App_ActiveRecord]
     */
    protected $_foreignInstances = array();

    /**
     * @var array[array]
     */
    protected $_links = array();

    /**
     * @param string $_table
     */
    public function __construct($_table = null)
    {
        $this->_table = is_null($_table) ? self::computeTable() : $_table;
    }

    /**
     * @return App_ActiveRecord
     */
    public static function createInstance()
    {
        $class = get_called_class();
        return new $class;
    }

	/**
	 * @return string
	 */
	public static function computeTable()
	{
	    $name = str_replace(array('Core_', 'App_', 'Cms_'), '', get_called_class());
	    return DB_PREFIX . Ext_String::underline($name);
	}

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * @return string
     */
    public static function getTbl()
    {
        return self::createInstance()->getTable();
    }

    /**
     * @param string $_name
     * @return boolean
     */
    public function hasAttribute($_name)
    {
        return key_exists($_name, $this->_attributes) ||
               key_exists(Ext_String::underline($_name), $this->_attributes);
    }

    /**
     * @param string $_name
     * @return boolean
     */
    public function __isset($_name)
    {
        return property_exists($this, $_name) || $this->hasAttribute($_name);
    }

    /**
     * Преобразоваывает и ищет атрибут, чтобы вернут его название. Имя id
     * преобразовывается в первичный ключ, *_id преобразовывается в первичный
     * ключ внешней таблицы.
     *
     * @param string $_name
     * @return string|array|false
     */
    public function getAttributeName($_name)
    {
        if (!property_exists($this, $_name)) {
            if (key_exists($_name, $this->_attributes)) {
                return $_name;
            }

            if ($_name == 'id') {
                return $this->getPrimaryKey()->getName();
            }

            $name = Ext_String::underline($_name);

            if (key_exists($name, $this->_attributes)) {
                return $name;
            }

            if (defined(DB_PREFIX) && DB_PREFIX) {
                $name = DB_PREFIX . $name;

                if (key_exists($name, $this->_attributes)) {
                    return $name;
                }
            }
        }

        return false;
    }

    /**
     * @param string $_name
     * @return string|number
     */
    public function __get($_name)
    {
        return property_exists($this, $_name)
             ? $this->$_name
             : $this->getAttrValue($_name);
    }

    /**
     * @param string $_name
     * @param string|number $_value
     */
    public function __set($_name, $_value)
    {
        if (property_exists($this, $_name)) {
            $this->$_name = $_value;

        } else {
            $this->setAttrValue($_name, $_value);
        }
    }

    /**
     * @param string $_name
     * @throws Exception
     * @return App_ActiveRecord_Attribute
     */
    public function getAttr($_name)
    {
        $name = $this->getAttributeName($_name);
        if ($name) {
            return $this->_attributes[$name];
        }

        throw new Exception("There is no a such property `$_name`.");
    }

    /**
     * @param string $_name
     * @return string|number
     */
    public function getAttrValue($_name)
    {
        return $this->getAttr($_name)->getValue();
    }

    /**
     * @param string $_name
     * @param string|number $_value
     */
    public function setAttrValue($_name, $_value)
    {
        $this->getAttr($_name)->setValue($_value);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $attrs = array();

        foreach ($this->_attributes as $attr) {
            $attrs[$attr->getName()] = $attr->getValue();
        }

        return $attrs;
    }

    /**
     * @param string $_prepend
     * @return array
     */
    public function getAttrNames($_prepend = false)
    {
        if ($_prepend) {
            $names = array();

            if ($_prepend === true)       $prepend = '`' . $this->getTable() . '`.';
            else if (!is_null($_prepend)) $prepend = "`$_prepend`.";
            else                          $prepend = '';

             foreach (array_keys($this->_attributes) as $name) {
                 $names[$name] = $prepend . $name;
             }

             return $names;

        } else {
            return array_keys($this->_attributes);
        }
    }

    /**
     * @param string $_name
     * @param string $_type
     * @return App_ActiveRecord_Attribute
     */
    public function addAttr($_name, $_type)
    {
        $this->_attributes[$_name] = new App_ActiveRecord_Attribute(
            $_name,
            $_type
        );

        return $this->_attributes[$_name];
    }

    /**
     * @param string $_name
     * @param string $_type
     * @throws Exception
     * @return App_ActiveRecord_Attribute
     */
    public function addPrimaryKey()
    {
        if (func_num_args() == 1) {
            $attr = $this->addAttr(
                $this->computePrimaryKeyName(),
                func_get_arg(0)
            );

        } else if (func_num_args() == 2) {
            $attr = $this->addAttr(func_get_arg(0), func_get_arg(1));

        } else {
            throw new Exception('Wrong number of arguments.');
        }

        $attr->isPrimary(true);
        return $attr;
    }

    /**
     * @param App_ActiveRecord $_instance
     * @return App_ActiveRecord_Attribute
     */
    public function addForeign(App_ActiveRecord $_instance)
    {
        $key = $_instance->getPrimaryKey();
        $this->_foreignInstances[$key->getName()] = $_instance;

        return $this->addAttr($key->getName(), $key->getType());
    }

    /**
     * @return array[App_ActiveRecord]
     */
    public function getForeignInstances()
    {
        return $this->_foreignInstances;
    }

    /**
     * @throws Exception
     * @return App_ActiveRecord_Attribute|array[App_ActiveRecord_Attribute]
     */
    public function getPrimaryKey()
    {
        $keys = array();

        if (!$this->_attributes) {
            throw new Exception();
        }

        foreach ($this->_attributes as $item) {
            if ($item->isPrimary()) {
                $keys[$item->getName()] = $item;
            }
        }

        if (count($keys) == 0)      throw new Exception('Primary key must be set');
        else if (count($keys) == 1) return current($keys);
        else                        return $keys;
    }

    /**
     * @param string $_prepend
     * @return string|array
     */
    public function getPrimaryKeyName($_prepend = null)
    {
        if ($_prepend === true)       $prepend = $this->getTable() . '.';
        else if (!is_null($_prepend)) $prepend = "$_prepend.";
        else                          $prepend = '';

        $primary = $this->getPrimaryKey();

        if (is_array($primary)) {
            foreach ($primary as $name => $attr) {
                $primary[$name] = $prepend . $attr->getName();
            }

            return $primary;

        } else {
            return $prepend . $primary->getName();
        }
    }

    /**
     * @param string $_prepend
     * @return string|array
     */
    public static function getPri($_prepend = null)
    {
        return self::createInstance()->getPrimaryKeyName($_prepend);
    }

    /**
     * @param string $_prepend
     * @return string
     */
    public static function getFirstForeignPri($_prepend = null)
    {
        $keys = array_values(self::getPri($_prepend));
        return $keys[0];
    }

    /**
     * @return string
     */
    public static function getFirstForeignTbl()
    {
        $instances = array_values(self::createInstance()->getForeignInstances());
        return $instances[0]->getTable();
    }

    /**
     * @param string $_prepend
     * @return string
     */
    public static function getSecondForeignPri($_prepend = null)
    {
        $keys = array_values(self::getPri($_prepend));
        return $keys[1];
    }

    /**
     * @return string
     */
    public static function getSecondForeignTbl()
    {
        $instances = array_values(self::createInstance()->getForeignInstances());
        return $instances[1]->getTable();
    }

	/**
	 * @return string
	 */
	public function computePrimaryKeyName()
	{
	    return $this->getTable() . '_id';
	}

    /**
     * @param string|array $_value
     * @param boolean $_isEqual
     * @return string
     */
    public function getPrimaryKeyWhere($_value = null, $_isEqual = true)
    {
        $where = array();
        $primary = $this->getPrimaryKey();
        $comp = $_isEqual ? '=' : '!=';

        if (is_array($primary)) {
            foreach ($primary as $name => $attr) {
                $value = is_null($_value)
                       ? $attr->getSqlValue()
                       : App_Db::escape($_value[$name]);

                $where[] = $attr->getName() . " $comp $value";
            }

        } else {
            $value = is_null($_value)
                   ? $primary->getSqlValue()
                   : App_Db::escape($_value);

            $where[] = $primary->getName() . " $comp $value";
        }

        return implode(' AND ', $where);
    }

    /**
     * @param string $_value
     * @return string
     */
    public function getPrimaryKeyWhereNot($_value = null)
    {
        return $this->getPrimaryKeyWhere($_value, false);
    }

    /**
     * @todo Замерить что работает быстрее $this->getId() или $this->id?
     * @param boolean $_isSql
     * @return string|array[string]
     */
    public function getId($_isSql = null)
    {
        $primary = $this->getPrimaryKey();

        if (is_array($primary)) {
            $ids = array();

            foreach ($primary as $attr) {
                $ids[$attr->getName()] = $_isSql
                                       ? $attr->getSqlValue()
                                       : $attr->getValue();
            }

            return $ids;

        } else {
            return $_isSql ? $primary->getSqlValue() : $primary->getValue();
        }
    }

    /**
     * @return string|number
     */
    public function getSqlId()
    {
        return $this->getId(true);
    }

    /**
     * @param string|integer $_id
     * @return App_ActiveRecord|false
     */
    public static function getById($_id)
    {
        return self::load($_id);
    }

    /**
     * @param string $_attr
     * @param string|integer $_value
     * @return App_ActiveRecord|false
     */
    public static function getBy($_attr, $_value)
    {
        return self::load($_value, $_attr);
    }

    /**
     * @param string|integer $_value
     * @param string $_attr
     * @return App_ActiveRecord|false
     */
    public static function load($_value, $_attr = null)
    {
        $obj = self::createInstance();
        $data = $obj->fetch($_value, $_attr);

        if ($data !== false) {
            $obj->fillWithData($data);
            return $obj;
        }

        return false;
    }

    /**
     * @param string|integer|array $_value
     * @param string|array $_attr
     * @return array|false
     */
    public function fetch($_value, $_attr = null)
    {
        if (is_array($_attr)) {
            $tmp = array();

            foreach ($_attr as $i => $attr) {
                $tmp[] = "$attr = " . App_Db::escape(
                             isset($_value[$attr]) ? $_value[$attr] : $_value[$i]
                         );
            }

            $where = implode(' AND ', $tmp);

        } else if ($_attr) {
            $where = "$_attr = " . App_Db::escape($_value);

        } else {
            $where = $this->getPrimaryKeyWhere($_value);
        }

        return App_Db::get()->getEntry("
            SELECT * FROM {$this->_table} WHERE $where LIMIT 1
        ");
    }

    /**
     * @param array $_data
     */
    public function fillWithData(array $_data)
    {
        foreach ($this->_attributes as $item) {
            if (key_exists($item->getName(), $_data)) {
                $item->setValue($_data[$item->getName()]);
            }
        }
    }

    /**
     * @return boolean
     */
    public function save()
    {
        return $this->id ? $this->update() : $this->create();
    }

    /**
     * @return boolean
     */
    public function create()
    {
        $values = array();

        foreach ($this->_attributes as $item) {
            if (!$item->isValue()) {
                if ($item->isPrimary()) {
                    if ($item->getType() == 'string') {
                        $item->setValue(App_Db::get()->getUnique(
                            $this->getTable(),
                            $item->getName()
                        ));
                    }

                } else if ($item->getName() == 'sort_order') {
                    $item->setValue(App_Db::get()->getNextNumber(
                        $this->getTable(),
                        $item->getName()
                    ));

                } else if (
                    $item->getName() == 'creation_date' ||
                    $item->getName() == 'creation_time'
                ) {
                    $item->setValue(
                        $item->getType() == 'integer' ? time() : date('Y-m-d H:i:s')
                    );
                }
            }

            $values[$item->getName()] = $item->getSqlValue();
        }

        $result = App_Db::get()->execute(
            'INSERT INTO ' . $this->getTable() .
            App_Db::get()->getQueryFields($values, 'insert', true)
        );

        if ($result) {
            $lastId = App_Db::get()->getLastInsertedId();
            if ($lastId) $this->id = $lastId;

            return true;
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function update()
    {
        $attrs = array();

        foreach ($this->_attributes as $attr) {
            if (!$attr->isPrimary()) {
                $attrs[$attr->getName()] = $attr->getSqlValue();
            }
        }

        return (boolean) App_Db::get()->execute(
            'UPDATE ' . $this->getTable() .
            App_Db::get()->getQueryFields($attrs, 'update', true) .
            'WHERE ' . $this->getPrimaryKeyWhere() .
            ' LIMIT 1'
        );
    }

    /**
     * @param string $_name
     * @param string|number $_value
     * @return boolean
     */
    public function updateAttr($_name, $_value = null)
    {
        if (!is_null($_value)) {
            $this->$_name = $_value;
        }

        $attrs = array($_name => $this->getAttr($_name)->getSqlValue());

        return (boolean) App_Db::get()->execute(
            'UPDATE ' . $this->getTable() .
            App_Db::get()->getQueryFields($attrs, 'update', true) .
            'WHERE ' . $this->getPrimaryKeyWhere() .
            ' LIMIT 1'
        );
    }

    /**
     * @return boolean
     */
    public function delete()
    {
        if (isset($this->_links)) {
            foreach (array_keys($this->_links) as $item) {
                $this->updateLinks($item);
            }
        }

        if (method_exists($this, 'getFiles')) {
            foreach ($this->getFiles() as $item) {
                $item->delete();
            }
        }

        return (boolean) App_Db::get()->execute(
            "DELETE FROM {$this->_table} WHERE " .
            $this->getPrimaryKeyWhere() .
            'LIMIT 1'
        );
    }

    /**
     * @return boolean
     */
    public static function truncate()
    {
        return (boolean) App_Db::get()->execute('TRUNCATE ' . self::getTbl());
    }

    /**
     * @param array $_where
     * @return boolean
     */
    public static function deleteWhere($_where)
    {
        return (boolean) App_Db::get()->execute(
            'DELETE FROM ' . self::getTbl() .
            ' WHERE ' . implode(' AND ', App_Db::get()->getWhere($_where))
        );
    }

//     Метод давно не использовался, поэтому его актуальность под вопросом.
//
//     public static function tableInit($_table, $_id = null, $_isLog = false)
//     {
//         $className = get_called_class();
//         $obj = new $className($_table);

//         if ($_isLog) {
//             $logFile = LIBRARIES . Ext_File::computeName($className) . '.txt';
//             Ext_File::write($logFile, $_table . PHP_EOL . PHP_EOL);
//             Ext_File::write(
//                 $logFile,
//                 'self::$Base = new App_ActiveRecord(self::TABLE);' . PHP_EOL
//             );
//         }

//         $attributes = App_Db::get()->getList("SHOW COLUMNS FROM $_table");

//         foreach ($attributes as $item) {
//             if ($item['Type'] == 'tinyint(1)') {
//                 $type = 'boolean';

//             } else if (preg_match(
//                 '/^([a-zA-Z]+)\((.+)\)$/',
//                 $item['Type'],
//                 $match
//             )) {
//                 $type = $match[1];

//             } else {
//                 $type = $item['Type'];
//             }

//             $method = (strpos($item['Key'], 'PRI') !== false)
//                     ? 'addPrimaryKey'
//                     : 'addAttr';

//             if ($_isLog) {
//                 Ext_File::write(
//                     $logFile,
//                     "self::\$_base->$method('{$item['Field']}', '$type');" .
//                     PHP_EOL
//                 );
//             }

//             $obj->$method($item['Field'], $type);
//         }

//         if ($_id) {
//             $obj->retrieve($_id);
//         }

//         return $obj;
//     }

    /**
     * @return string|false
     */
    public function getSortAttrName()
    {
        foreach (array('sort_order', 'title', 'name') as $name) {
            if ($this->hasAttribute($name)) {
                return $name;
            }
        }

        return false;
    }

    /**
     * @param array $_where
     * @param array $_params
     * @return array[App_ActiveRecord]
     */
    public static function getList($_where = null, $_params = array())
    {
        $instance = self::createInstance();
        $list = array();

        $items = App_Db::get()->getList(App_Db::get()->getSelect(
            $instance->getTable(),
            null,
            $_where,
            empty($_params['order']) ? $instance->getSortAttrName() : $_params['order'],
            null,
            empty($_params['limit']) ? null : (int) $_params['limit'],
            empty($_params['offset']) ? null : (int) $_params['offset']
        ));

        foreach ($items as $item) {
            $obj = self::createInstance();
            $obj->fillWithData($item);
            $list[is_array($obj->getId()) ? implode('-', $obj->getId()) : $obj->getId()] = $obj;
        }

        return $list;
    }

    /**
     * @param array $_where
     * @return integer
     */
    public static function getCount($_where = array())
    {
        $result = App_Db::get()->getEntry(App_Db::get()->getSelect(
            self::getTbl(),
            'COUNT(1) AS `cnt`',
            $_where
        ));

        return $result ? (int) $result['cnt'] : 0;
    }

    /**
     * @param string $_attr
     * @param string $_value
     * @param string|array $_excludeId
     * @return boolean
     */
    public static function isUnique($_attr, $_value, $_excludeId = null)
    {
        $where = array($_attr => $_value);

        if ($_excludeId) {
            $where = array_merge(
                $where,
                App_Db::get()->getWhereNot(array(self::getPri() => $_excludeId))
            );
        }

        return count(self::getList($where, array('limit' => 1))) == 0;
    }

    /**
     * @param string $_name
     * @param array $_value
     */
    public function updateLinks($_name, $_value = null)
    {
        if ($this->getLinks($_name)) {
            foreach ($this->getLinks($_name) as $item) {
                $item->delete();
            }

            $this->setLinks($_name);
        }

        if (!empty($_value)) {
            $this->setLinks($_name, $_value);

            foreach ($this->getLinks($_name) as $item) {
                $item->create();
            }
        }
    }

    public function getLinks($_name, $_isPublished = null)
    {
        if (!isset($this->_links[$_name])) {
            if (isset($this->_linkParams[$_name])) {
                $class = $this->_linkParams[$_name];
                $where = array($this->getPrimaryKeyWhere());

                if (!is_null($_isPublished)) {
                    $where['is_published'] = (boolean) $_isPublished ? 1 : 0;
                }

                $this->_links[$_name] = $class::getList($where);

            } else {
                $this->_links[$_name] = array();
            }
        }

        return $this->_links[$_name];
    }

    public function getLinkIds($_name, $_isPublished = null)
    {
        $result = array();

        if (isset($this->_linkParams[$_name])) {
            $class = $this->_linkParams[$_name];

            $keys = array(
                $class::getFirstForeignPri(),
                $class::getSecondForeignPri()
            );

            $key = $this->getPrimaryKeyName() == $keys[0]
                 ? $keys[1]
                 : $keys[0];

            foreach ($this->getLinks($_name, $_isPublished) as $item) {
                $result[] = $item->$key;
            }
        }

        return $result;
    }

    public function setLinks($_name, $_values = null)
    {
        $this->_links[$_name] = array();

        if (!empty($_values) && isset($this->_linkParams[$_name])) {
            $values = is_array($_values) ? $_values : array($_values);
            $class = $this->_linkParams[$_name];

            $keys = array(
                $class::getFirstForeignPri(),
                $class::getSecondForeignPri()
            );

            $pri = $this->getPrimaryKeyName();
            $key = $pri == $keys[0] ? $keys[1] : $keys[0];

            foreach ($values as $id => $item) {
                $obj = new $class;
                $obj->$pri = $this->id;

                if (is_array($item)) {
                    $obj->$key = $id;

                    foreach ($item as $attribute => $value) {
                        $obj->$attribute = $value;
                    }

                } else {
                    $obj->$key = $item;
                }

                $this->_links[$_name][] = $obj;
            }
        }
    }
}
