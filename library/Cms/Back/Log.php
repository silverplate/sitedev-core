<?php

abstract class Core_Cms_Back_Log extends App_Model
{
    const ACT_LOGIN      = 1;
    const ACT_LOGOUT     = 2;
    const ACT_CREATE     = 3;
    const ACT_MODIFY     = 4;
    const ACT_DELETE     = 5;
    const ACT_REMIND_PWD = 6;
    const ACT_CHANGE_PWD = 7;

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('integer');
        $this->addForeign(App_Cms_Back_User::createInstance());
        $this->addForeign(App_Cms_Back_Section::createInstance());
        $this->addAttr('section_name', 'string');
        $this->addAttr('user_ip', 'string');
        $this->addAttr('user_agent', 'string');
        $this->addAttr('request_uri', 'string');
        $this->addAttr('request_get', 'string');
        $this->addAttr('request_post', 'string');
        $this->addAttr('cookies', 'string');
        $this->addAttr('script_name', 'string');
        $this->addAttr('action_id', 'integer');
        $this->addAttr('entry_id', 'string');
        $this->addAttr('description', 'string');
        $this->addAttr('creation_date', 'datetime');
    }

    public static function getActions()
    {
        return array(
            self::ACT_LOGIN => 'Авторизация',
            self::ACT_LOGOUT => 'Окончание работы',
            self::ACT_CREATE => 'Создание',
            self::ACT_MODIFY => 'Изменение',
            self::ACT_DELETE => 'Удаление',
            self::ACT_REMIND_PWD => 'Напоминание пароля',
            self::ACT_CHANGE_PWD => 'Смена пароля'
        );
    }

    public static function logModule($_actionId, $_entryId, $_description = null)
    {
        return self::log(
            $_actionId,
            array('entry_id' => $_entryId, 'description' => $_description)
        );
    }

    public static function log($_actionId, $_params = array())
    {
        global $g_user, $g_section;

        $params = array(
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'request_uri' => $_SERVER['REQUEST_URI'],
            'request_get' => serialize($_GET),
            'request_post' => serialize($_POST),
            'cookies' => serialize($_SERVER['HTTP_COOKIE']),
            'script_name' => $_SERVER['SCRIPT_NAME'],
            'action_id' => $_actionId,
            'entry_id' => isset($_params['entry_id']) ? $_params['entry_id'] : '',
            'description' => isset($_params['description']) ? $_params['description'] : ''
        );

        $userKey = App_Cms_Back_User::getPri();
        $sectionKey = App_Cms_Back_Section::getPri();

        if (isset($_params['section'])) {
            $params[$sectionKey] = $_params['section']->getId();
            $params['section_name'] = $_params['section']->getTitle();

        } else if ($g_section) {
            $params[$sectionKey] = $g_section->getId();
            $params['section_name'] = $g_section->getTitle();

        } else if (
            isset($_params['section_id']) &&
            isset($_params['section_name'])
        ) {
            $params[$sectionKey] = $_params['section_id'];
            $params['section_name'] = $_params['section_name'];

        } else {
            $section = App_Cms_Back_Section::compute();

            if ($section) {
                $params[$sectionKey] = $section->getId();
                $params['section_name'] = $section->getTitle();
            }
        }

        if (isset($_params['user'])) {
            $params[$userKey] = $_params['user']->getId();
            $params['user_name'] = $_params['user']->getTitle();

        } else if ($g_user) {
            $params[$userKey] = $g_user->getId();
            $params['user_name'] = $g_user->getTitle();

        } else if (
            isset($_params['user_id']) &&
            isset($_params['user_name'])
        ) {
            $params[$userKey] = $_params['user_id'];
            $params['user_name'] = $_params['user_name'];
        }

        $obj = self::createInstance();
        $obj->fillWithData($params);
        $obj->create();

        return $obj;
    }

    public function getBackOfficeXml($_xml = null)
    {
        $attrs = array('date' => $this->creationDate);

        foreach (array('entry_id', 'user_ip', 'script_name', 'action_id') as $item) {
            if ($this->$item) {
                $attrs[$item] = $this->$item;
            }
        }

        $xml = empty($_xml) ? array() : $_xml;
        if (!is_array($xml)) $xml = array($xml);

        foreach (array('user_agent', 'description') as $item) {
            Ext_Xml::append($xml, Ext_Xml::notEmptyCdata($item, $this->$item));
        }

        return parent::getXml('item', $xml, $attrs);
    }

    /**
     * @param array $_where
     * @return array
     */
    public static function getQueryConditions($_where = array())
    {
        $where = array();

        if (isset($_where['from_date'])) {
            $where[] = 'creation_date >= ' . date('"Y-m-d 00:00:00"', $_where['from_date']);
            unset($_where['from_date']);
        }

        if (isset($_where['till_date'])) {
            $where[] = 'creation_date <= ' . date('"Y-m-d 23:59:59"', $_where['till_date']);
            unset($_where['till_date']);
        }

        return array_merge($where, $_where);
    }

    /**
     * @param array $_where
     * @param array $_params
     * @return array[App_Cms_Back_Log]
     */
    public static function getList($_where = array(), $_params = array())
    {
        $params = $_params;
        if (!isset($params['order'])) {
            $params['order'] = 'creation_date DESC';
        }

        return parent::getList(self::getQueryConditions($_where), $params);
    }

    /**
     * @param array $_where
     * @return integer
     */
    public static function getCount($_where = array())
    {
        return parent::getCount(self::getQueryConditions($_where));
    }
}
