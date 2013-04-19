<?php

abstract class Core_Cms_Session
{
    const NAME = 'sess';
    const PATH = '/';

    const ACT_PARAM = 'action';
    const ACT_PARAM_NEXT = 'action_next';
    const ACT_START = 1;
    const ACT_LOGIN = 2;
    const ACT_LOGIN_ERROR = 3;
    const ACT_CONTINUE = 4;
    const ACT_LOGOUT = 5;
    const ACT_REMIND_PWD = 6;
    const ACT_REMIND_PWD_ERROR = 7;
    const ACT_CHANGE_PWD = 8;
    const ACT_CHANGE_PWD_ERROR = 9;

    private $_isLoggedIn;
    private $_userId;
    private $_params = array();
    private $_cookieName;
    private $_cookiePath;
    private static $_obj;

    /**
     * @return App_Cms_Session
     */
    public static function get()
    {
        if (!isset(self::$_obj)) {
            $class = get_called_class();

            self::$_obj = new $class;
            self::$_obj->_init();
        }

        return self::$_obj;
    }

    public static function getTbl()
    {
        return Ext_Db::get()->getPrefix() . 'session';
    }

    public static function getPri()
    {
        return Ext_Db::get()->getPrefix() . 'session_id';
    }

    protected function __construct()
    {
        $this->setCookiePath($this->getCookiePath());
        $this->setCookieName($this->getCookieName());
    }

    protected function _init()
    {
        $userAgent = md5(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
        $session = Ext_Db::get()->getEntry('
            SELECT
                is_logged_in,
                user_id
            FROM
                ' . self::getTbl() . '
            WHERE
                ' . self::getPri() . ' = ' . Ext_Db::escape(self::getId()) . ' AND
                user_agent = ' . Ext_Db::escape($userAgent) . ' AND (
                    ISNULL(valid_date) OR NOW() < valid_date
                ) AND (
                    life_span <= 0 OR DATE_ADD(creation_date, INTERVAL life_span MINUTE) < NOW()
                ) AND (
                    timeout <= 0 OR (NOT(ISNULL(last_impression_date)) AND DATE_ADD(last_impression_date, INTERVAL timeout MINUTE) < NOW())
                ) AND (
                    is_ip_match = 0 OR user_ip = ' . Ext_Db::escape($_SERVER['REMOTE_ADDR']) . '
                )
        ');

        if ($session) {
            foreach (Ext_Db::get()->getList('SELECT name, value FROM ' . self::getTbl() . '_param WHERE ' . self::getPri() . ' = ' . Ext_Db::escape(self::getId())) as $item) {
                $this->_params[$item['name']] = unserialize($item['value']);
            }

            $this->impress();

        } else {
            self::_destroy();
            self::clean();

            Ext_Db::get()->execute('INSERT INTO ' . self::getTbl() . Ext_Db::get()->getQueryFields(array(
                self::getPri() => Ext_Db::escape(self::getId()),
                'is_ip_match' => 0,
                'is_logged_in' => 0,
                'user_id' => 0,
                'user_agent' => Ext_Db::escape($userAgent),
                'user_ip' => Ext_Db::escape($_SERVER['REMOTE_ADDR']),
                'life_span' => 0,
                'timeout' => 0,
                'creation_date' => 'NOW()',
                'last_impression_date' => 'NOW()',
                'valid_date' => 'NULL'
            ), 'insert', true));
        }

        $this->_isLoggedIn = $session && $session['is_logged_in'] == 1;
        $this->_userId = $session ? $session['user_id'] : null;
    }

    public function setCookieName($_name)
    {
        $this->_cookieName = $_name;
    }

    public function getCookieName()
    {
        if ($this->_cookieName) {
            return $this->_cookieName;
        } else {
            $name = trim($this->getCookiePath(), '/');
            $name = self::NAME . ($name ? '_' . $name : '');
            return $name;
        }
    }

    public function setCookiePath($_path)
    {
        $this->_cookiePath = $_path;
    }

    public function getCookiePath()
    {
        if ($this->_cookiePath) {
            return $this->_cookiePath;
        } else {
            $url = Ext_File::parseUrl();
            preg_match('/^(\/(admin|cms)\/)/', $url['path'], $match);
            return $match ? $match[1] : self::PATH;
        }
    }

    public static function getId()
    {
        if (empty($_COOKIE[self::get()->getCookieName()])) {
            self::_setId(Ext_Db::get()->getUnique(self::getTbl(), self::getPri(), 30));
        }

        return $_COOKIE[self::get()->getCookieName()];
    }

    private static function _setId($_id, $_expires = null)
    {
        $_COOKIE[self::get()->getCookieName()] = $_id;
        setcookie(self::get()->getCookieName(), $_id, $_expires, self::get()->getCookiePath());
    }

    public function isLoggedIn()
    {
        return (boolean) $this->_isLoggedIn;
    }

    public function getUserId()
    {
        return $this->_userId;
    }

    public function login($_userId,
                          $_isIpMatch = false,
                          $_lifeSpan = null,
                          $_timeout = null,
                          $_validDate = null)
    {
        $this->_isLoggedIn = true;
        $this->_userId = $_userId;
        self::_setId(self::getId(), $_validDate ? $_validDate : null);

        Ext_Db::get()->execute('UPDATE ' . self::getTbl() . Ext_Db::get()->getQueryFields(array(
            'is_ip_match' => ($_isIpMatch) ? 1 : 0,
            'is_logged_in' => 1,
            'user_id' => Ext_Db::escape($_userId),
            'life_span' => $_lifeSpan ? $_lifeSpan : 0,
            'timeout' => $_timeout ? $_timeout : 0,
            'valid_date' => $_validDate ? Ext_Db::escape(date('Y-m-d H:i:s', $_validDate)) : 'NULL'
        ), 'update', true) . 'WHERE ' . self::getPri() . ' = ' . Ext_Db::escape(self::getId()));
    }

    public function logout()
    {
        $this->_isLoggedIn = false;
        $this->_userId = '';

        Ext_Db::get()->execute('UPDATE ' . self::getTbl() . Ext_Db::get()->getQueryFields(array(
            'is_logged_in' => 0,
            'user_id' => 0,
            'valid_date' => 'NULL'
        ), 'update', true) . 'WHERE ' . self::getPri() . ' = ' . Ext_Db::escape(self::getId()));
    }

    private function impress()
    {
        Ext_Db::get()->execute('UPDATE ' . self::getTbl() . Ext_Db::get()->getQueryFields(array('last_impression_date' => 'NOW()'), 'update', true) . 'WHERE ' . self::getPri() . ' = ' . Ext_Db::escape(self::getId()));
    }

    private function _initParam($_name, $_value)
    {
        $this->_params[$_name] = unserialize($_value);
    }

    public function deleteParam($_name)
    {
        Ext_Db::get()->execute('DELETE FROM ' . self::getTbl() . '_param WHERE ' . self::getPri() . ' = ' . Ext_Db::escape(self::getId()) . ' AND name = ' . Ext_Db::escape($_name));
        unset($this->_params[$_name]);
    }

    public function setParam($_name, $_value)
    {
        self::deleteParam($_name);

        Ext_Db::get()->execute('INSERT INTO ' . self::getTbl() . '_param' . Ext_Db::get()->getQueryFields(array(
            self::getPri() => self::getId(),
            'name' => $_name,
            'value' => serialize($_value)
        ), 'insert'));

        $this->_params[$_name] = $_value;
    }

    public function getParam($_name)
    {
        if (!isset($this->_params[$_name])) {
            $param = Ext_Db::get()->getEntry('SELECT value FROM ' . self::getTbl() . '_param WHERE ' . self::getPri() . ' = ' . Ext_Db::escape(self::getId()) . ' AND name = ' . Ext_Db::escape($_name));
            $this->_params[$_name] = $param ? unserialize($param['value']) : null;
        }

        return $this->_params[$_name];
    }

    private function _destroy()
    {
        Ext_Db::get()->execute('DELETE FROM ' . self::getTbl() . ' WHERE ' . self::getPri() . ' = ' . Ext_Db::escape(self::getId()));
        Ext_Db::get()->execute('DELETE FROM ' . self::getTbl() . '_param WHERE ' . self::getPri() . ' = ' . Ext_Db::escape(self::getId()));
    }

    public static function clean($_userId = null)
    {
        Ext_Db::get()->execute('
            DELETE FROM
                ' . self::getTbl() . '
            WHERE
                ' . ($_userId ? 'user_id = ' . Ext_Db::escape($_userId) . ' OR ' : '') . '
                (NOT(ISNULL(valid_date)) AND valid_date < NOW()) OR
                (ISNULL(valid_date) AND DATE_ADD(last_impression_date, INTERVAL 1 DAY) < NOW()) OR
                (life_span > 0 AND DATE_ADD(creation_date, INTERVAL life_span MINUTE) < NOW()) OR
                (timeout > 0 AND (ISNULL(last_impression_date) OR DATE_ADD(last_impression_date, INTERVAL timeout MINUTE) < NOW()))
        ');

        Ext_Db::get()->execute('DELETE FROM ' . self::getTbl() . '_param WHERE ' . self::getPri() . ' NOT IN (SELECT ' . self::getPri() . ' FROM ' . self::getTbl() . ')');
    }

    public function getXml($_node = null, $_xml = null)
    {
        $attrs = array('id' => self::getId());

        if ($this->getParam(self::ACT_PARAM)) {
            $attrs[self::ACT_PARAM] = $this->getParam(self::ACT_PARAM);
        }

        return Ext_Xml::node($_node ? $_node : 'session', $_xml, $attrs);
    }

    public function getWorkmateXml()
    {
        $xml = '';

        foreach ($this->getWorkmates() as $item) {
            Ext_Xml::append($xml, Ext_Xml::cdata(
                'back-user',
                empty($item['title']) ? $item['login'] : $item['title']
            ));
        }

        return Ext_Xml::notEmptyNode('workmates', $xml);
    }

    /**
     * @todo В таблицу с сессиями попадают пользователи и сайта и СУ, возможно
     * пересечение ID пользователей, нужно различать типы пользователей.
     */
    public function getWorkmates()
    {
        if ($this->isLoggedIn()) {
            return Ext_Db::get()->getList('
                SELECT
                    u.title,
                    u.login
                FROM
                    `' . self::getTbl() . '` AS s,
                    ' . Ext_Db::get()->getPrefix() . 'back_user AS u
                WHERE
                    DATE_ADD(s.last_impression_date, INTERVAL 15 MINUTE) > NOW() AND
                    s.user_id != 0 AND
                    s.' . self::getPri() . ' != ' . Ext_Db::escape($this->getId()) . ' AND
                    s.user_id = u.' . Ext_Db::get()->getPrefix() . 'back_user_id
            ');
        }

        return array();
    }
}
