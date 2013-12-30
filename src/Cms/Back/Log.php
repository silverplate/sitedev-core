<?php

namespace Core\Cms\Back;

abstract class Log extends \App\ActiveRecord
{
    const ACT_LOGIN      = 1;
    const ACT_LOGOUT     = 2;
    const ACT_CREATE     = 3;
    const ACT_MODIFY     = 4;
    const ACT_DELETE     = 5;
    const ACT_REMIND_PWD = 6;
    const ACT_CHANGE_PWD = 7;

    /**
     * @var \App\Cms\Back\User
     */
    protected $_user;

    /**
     * @var \App\Cms\Back\Section
     */
    protected $_section;

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('integer');
        $this->addForeign(\App\Cms\Back\User::createInstance());
        $this->addForeign(\App\Cms\Back\Section::createInstance());
        $this->addAttr('section_name', 'string');
        $this->addAttr('user_name', 'string');
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

    /**
     * @return \App\Cms\Back\User
     */
    public function getUser()
    {
        if (!isset($this->_user)) {
            $this->_user = \App\Cms\Back\User::getById($this->backUserId);
        }

        return $this->_user;
    }

    /**
     * @return \App\Cms\Back\Section
     */
    public function getSection()
    {
        if (!isset($this->_section)) {
            $this->_section = \App\Cms\Back\Section::getById($this->backSectionId);
        }

        return $this->_section;
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

    public static function getActionTitleById($_id)
    {
        $actions = static::getActions();
        return isset($actions[$_id]) ? $actions[$_id] : false;
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

        $params = array(
            'request_get' => $_GET,
            'request_post' => $_POST,
            'action_id' => $_actionId,
            'entry_id' => isset($_params['entry_id']) ? $_params['entry_id'] : '',
            'description' => isset($_params['description']) ? $_params['description'] : ''
        );

        $keys = array(
            'user_ip' => 'REMOTE_ADDR',
            'user_agent' => 'HTTP_USER_AGENT',
            'request_uri' => 'REQUEST_URI',
            'cookies' => 'HTTP_COOKIE',
            'script_name' => 'SCRIPT_NAME'
        );

        foreach ($keys as $attr => $key) {
            if (!empty($_SERVER[$key])) {
                $params[$attr] = $_SERVER[$key];
            }
        }

        foreach (array('request_get', 'request_post', 'cookies') as $item) {
            if (key_exists($item, $params)) {
                $params[$item] = serialize($params[$item]);
            }
        }

        $userKey = \App\Cms\Back\User::getPri();
        $sectionKey = \App\Cms\Back\Section::getPri();

        if (isset($_params['section'])) {
            $params[$sectionKey] = $_params['section']->getId();
            $params['section_name'] = $_params['section']->getTitle();

        } else if (\App\Cms\Back\Section::get()) {
            $params[$sectionKey] = \App\Cms\Back\Section::get()->getId();
            $params['section_name'] = \App\Cms\Back\Section::get()->getTitle();

        } else if (
            isset($_params['section_id']) &&
            isset($_params['section_name'])
        ) {
            $params[$sectionKey] = $_params['section_id'];
            $params['section_name'] = $_params['section_name'];

        } else {
            $section = \App\Cms\Back\Section::compute();

            if ($section) {
                $params[$sectionKey] = $section->getId();
                $params['section_name'] = $section->getTitle();
            }
        }

        if (isset($_params['user'])) {
            $params[$userKey] = $_params['user']->getId();
            $params['user_name'] = $_params['user']->getTitle();

        } else if (\App\Cms\Back\User::get()) {
            $params[$userKey] = \App\Cms\Back\User::get()->getId();
            $params['user_name'] = \App\Cms\Back\User::get()->getTitle();

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

    public function getBackOfficeXml($_node = null, $_xml = null, $_attrs = null)
    {
        $node = empty($_node) ? 'item' : $_node;

        $attrs = empty($_attrs) ? array() : $_attrs;
        $attrs['date'] = $this->creationDate;

        foreach (array('entry_id', 'user_ip', 'script_name', 'action_id') as $item) {
            if ($this->$item) {
                $attrs[$item] = $this->$item;
            }
        }

        if (empty($_xml))         $xml = array();
        else if (is_array($_xml)) $xml = $_xml;
        else                      $xml = array($_xml);

        foreach (array('user_agent', 'description') as $item) {
            \Ext\Xml::append($xml, \Ext\Xml::notEmptyCdata($item, $this->$item));
        }

        \Ext\Xml::append($xml, \Ext\Xml::notEmptyCdata(
            'user',
            $this->getUser() ? $this->getUser()->getTitle() : $this->userName
        ));

        \Ext\Xml::append($xml, \Ext\Xml::notEmptyCdata(
            'section',
            $this->getSection() ? $this->getSection()->getTitle() : $this->sectionName
        ));

        \Ext\Xml::append($xml, \Ext\Xml::notEmptyCdata(
            'action',
            static::getActionTitleById($this->actionId)
        ));

        return parent::getXml($node, $xml, $attrs);
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
     * @return array[\App\Cms\Back\Log]
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

    /**
     * @return \App\Cms\Back\Office_NavFilter
     */
    public static function getCmsNavFilter()
    {
        $filter = new \App\Cms\Back\Office_NavFilter(get_called_class());
        $filter->setType('content-filter');


        // Дата

        $filter->addElement(new \App\Cms\Back\Office_NavFilter_Element_Date(
            'creation_date',
            'sql',
            'Время'
        ));


        // Пользователи

        $el = new \App\Cms\Back\Office_NavFilter_Element_Multiple(
            \App\Cms\Back\User::getPri(),
            'Пользователи'
        );

        foreach (\App\Cms\Back\User::getList() as $item) {
            $el->addOption($item->id, $item->getTitle());
        }

        $filter->addElement($el);


        // Разделы

        $el = new \App\Cms\Back\Office_NavFilter_Element_Multiple(
            \App\Cms\Back\Section::getPri(),
            'Разделы'
        );

        foreach (\App\Cms\Back\Section::getList() as $item) {
            $el->addOption($item->id, $item->getTitle());
        }

        $filter->addElement($el);


        // Действия

        $el = new \App\Cms\Back\Office_NavFilter_Element_Multiple(
            'action_id',
            'Действия'
        );

        foreach (self::getActions() as $id => $title) {
            $el->addOption($id, $title);
        }

        $filter->addElement($el);
        $filter->run();

        return $filter;
    }
}
