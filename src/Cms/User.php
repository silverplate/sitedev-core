<?php

namespace Core\Cms;

abstract class User extends \App\ActiveRecord
{
    const AUTH_GROUP_GUESTS = 1;
    const AUTH_GROUP_USERS  = 2;
    const AUTH_GROUP_ALL    = 3; // Сумма всех констант

    protected static $_siteUser;

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('integer');
        $this->addAttr('status_id', 'integer');
        $this->addAttr('first_name', 'string');
        $this->addAttr('last_name', 'string');
        $this->addAttr('middle_name', 'string');
        $this->addAttr('email', 'string');
        $this->addAttr('phone_code', 'string');
        $this->addAttr('phone', 'string');
        $this->addAttr('passwd', 'string');
        $this->addAttr('creation_time', 'integer');
        $this->addAttr('reminder_key', 'string');
        $this->addAttr('reminder_time', 'integer');
    }

    public static function getAuthGroups()
    {
        return array(
            self::AUTH_GROUP_ALL => array(
                'title' => 'Все', 'title1' => 'Всем'
            ),
            self::AUTH_GROUP_GUESTS => array(
                'title' => 'Неавторизованные', 'title1' => 'Неавторизованным'
            ),
            self::AUTH_GROUP_USERS => array(
                'title' => 'Авторизованные',
                'title1' => 'Авторизованным'
            )
        );
    }

    public static function getAuthGroupTitle($_id, $_title = null)
    {
        $title = 'title' . ($_title ? "_$_title" : '');
        $groups = self::getAuthGroups();

        return isset($groups[$_id]) ? $groups[$_id][$title] : false;
    }

    public static function getAuthGroup()
    {
        global $gIsUsers;

        if (empty($gIsUsers)) return null;
        else if (self::get()) return self::AUTH_GROUP_USERS;
        else                  return self::AUTH_GROUP_GUESTS;
    }

    public static function get()
    {
        return self::$_siteUser;
    }

    public static function startSession()
    {
        $session = \App\Cms\Session::get();

        if (isset($_POST['auth_submit']) || isset($_POST['auth_submit_x'])) {
            $try = self::auth($_POST['auth_login'], $_POST['auth_password']);

            if ($try) {
                $session->login($try->getId());
                $session->setParam(
                    \App\Cms\Session::ACT_PARAM_NEXT,
                    \App\Cms\Session::ACT_LOGIN
                );

            } else {
                $session->setParam(
                    \App\Cms\Session::ACT_PARAM_NEXT,
                    \App\Cms\Session::ACT_LOGIN_ERROR
                );
            }

            reload();

        } else if (
            isset($_POST['auth_reminder_submit']) ||
            isset($_POST['auth_reminder_submit_x'])
        ) {
            $try = !empty($_POST['auth_email'])
                 ? self::getList(array('email' => $_POST['auth_email'], 'status_id' => 1))
                 : false;

            if ($try) {
                foreach ($try as $user) {
                    $session->setParam(
                        \App\Cms\Session::ACT_PARAM_NEXT,
                        \App\Cms\Session::ACT_REMIND_PWD
                    );

                    $user->remindPassword();
                }

            } else {
                $session->setParam(
                    \App\Cms\Session::ACT_PARAM_NEXT,
                    \App\Cms\Session::ACT_REMIND_PWD_ERROR
                );
            }

            reload();

        } else if (
            isset($_GET['r']) ||
            (isset($_GET['e']) && $session->isLoggedIn())
        ) {
            if ($session->isLoggedIn()) {
                $session->logout();
            }

            if (isset($_GET['r'])) {
                $try = $_GET['r'] ? self::load($_GET['r'], 'reminder_key') : false;

                $session->setParam(
                    \App\Cms\Session::ACT_PARAM_NEXT,
                    $try && $try->changePassword() == 0 ? \App\Cms\Session::ACT_CHANGE_PWD : \App\Cms\Session::ACT_CHANGE_PWD_ERROR
                );

            } else {
                $session->setParam(
                    \App\Cms\Session::ACT_PARAM_NEXT,
                    \App\Cms\Session::ACT_LOGOUT
                );
            }

            reload();

        } else if (isset($_GET['e']) && $session->isLoggedIn()) {
            $session->logout();

            $session->setParam(
                \App\Cms\Session::ACT_PARAM_NEXT,
                \App\Cms\Session::ACT_LOGOUT
            );

            reload();

        } else {
            $session->setParam(
                \App\Cms\Session::ACT_PARAM,
                $session->getParam(\App\Cms\Session::ACT_PARAM_NEXT) ? $session->getParam(\App\Cms\Session::ACT_PARAM_NEXT) : \App\Cms\Session::ACT_START
            );

            $session->setParam(
                \App\Cms\Session::ACT_PARAM_NEXT,
                \App\Cms\Session::ACT_CONTINUE
            );

            self::$_siteUser = $session->isLoggedIn()
                             ? self::auth($session->getUserId())
                             : false;
        }
    }

    public function checkUnique()
    {
        return self::isUnique(
            'email',
            $this->email,
            $this->id ? $this->id : null
        );
    }

    /**
     * @return \App\Cms\User
     */
    public static function auth()
    {
        if (func_num_args() == 1) {
            $user = self::getById(func_get_arg(0));

        } else if (func_num_args() == 2) {
            $user = self::getBy(
                array('email', 'passwd'),
                array(func_get_arg(0), self::cryptPassword(func_get_arg(1)))
            );
        }

        return !empty($user) && $user->statusId == 1 ? $user : false;
    }

    public function getMessageUrl()
    {
        global $gHost;
        return 'http://' . $gHost;
    }

    public function remindPassword()
    {
        if ($this->email) {
            $this->reminderTime = time();
            $this->reminderKey = \Ext\Db::get()->getUnique(
                $this->getTable(),
                'reminder_key',
                30
            );

            $this->update();
            $url = $this->getMessageUrl();

            $message =
                "Для смены пароля к сайту $url загрузите страницу $url" .
                '/?r=' . $this->reminderKey . "\n\n" .
                'Если вы не просили поменять пароль, проигнорируйте это сообщение.';

            return \App\Cms\Mail::forcePost(
                $this->email,
                $message,
                'Смена пароля'
            );
        }
    }

    public function changePassword()
    {
        if ($this->email) {
            if (
                $this->statusId == 1 &&
                $this->reminderTime &&
                $this->reminderTime > time() - 60 * 60 * 24
            ) {
                $password = $this->generatePassword();

                $this->setPassword($password);
                $this->reminderKey = '';
                $this->reminderTime = '';
                $this->update();

                $url = $this->getMessageUrl();
                $message = "Доступ к сайту $url.\n\n" .
                           "Логин: {$this->email}\nПароль: $password";

                return \App\Cms\Mail::forcePost($this->email, $message, 'Доступ')
                     ? 0
                     : 3;
            }

            return 2;
        }

        return 1;
    }

    public function getTitle()
    {
        return trim($this->lastName . ' ' . $this->firstName);
    }

    public static function generatePassword()
    {
        return \Ext\String::getRandomReadableAlt(8);
    }

    public static function cryptPassword($_password)
    {
        $class = get_called_class();
        return crypt($_password, $class::SECRET);
    }

    public function setPassword($_password)
    {
        $this->passwd = self::cryptPassword($_password);
    }

    public function updatePassword($_password)
    {
        $this->updateAttr('passwd', self::cryptPassword($_password));
    }

    public function getBackOfficeXml($_xml = array(), $_attrs = array())
    {
        $attrs = $_attrs;

        if (!isset($attrs['is_published']) && $this->statusId == 1) {
            $attrs['is-published'] = 1;
        }

        return parent::getBackOfficeXml($_xml, $attrs);
    }

    /**
     * @return \App\Cms\Back\Office\NavFilter
     */
    public static function getCmsNavFilter()
    {
        $filter = new \App\Cms\Back\Office\NavFilter(get_called_class());

        $filter->addElement(new \App\Cms\Back\Office\NavFilter\Element\Name(
            'Имя'
        ));

        $filter->addElement(new App\Cms\Back\Office\NavFilter\Element(
            'email',
            'Электронная почта'
        ));

        $filter->run();

        return $filter;
    }

    /**
     * @param array $_where
     * @param array $_params
     * @return \App\Cms\User[]
     */
    public static function getList($_where = null, $_params = array())
    {
        $params = empty($_params) ? array() : $_params;

        if (!isset($params['order'])) {
            $params['order'] = 'CONCAT_WS("", last_name, first_name, middle_name)';
        }

        return parent::getList($_where, $params);
    }
}
