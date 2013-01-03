<?php

abstract class Core_Cms_User extends App_Model
{
    const AUTH_GROUP_GUESTS = 1;
    const AUTH_GROUP_USERS  = 2;
    const AUTH_GROUP_ALL    = 3; // Сумма всех констант

    protected static $_siteUser;

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('string');
        $this->addAttr('status_id', 'integer');
        $this->addAttr('first_name', 'string');
        $this->addAttr('last_name', 'string');
        $this->addAttr('patronymic_name', 'string');
        $this->addAttr('email', 'string');
        $this->addAttr('phone_code', 'string');
        $this->addAttr('phone', 'string');
        $this->addAttr('passwd', 'string');
        $this->addAttr('reminder_key', 'string');
        $this->addAttr('reminder_date', 'datetime');
        $this->addAttr('creation_date', 'datetime');
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
        if (!defined('IS_USERS') || !IS_USERS) return null;
        else if (self::get())                  return self::AUTH_GROUP_USERS;
        else                                   return self::AUTH_GROUP_GUESTS;
    }

    public static function get()
    {
        return self::$_siteUser;
    }

    public static function startSession()
    {
        $session = App_Cms_Session::get();

        if (isset($_POST['auth_submit']) || isset($_POST['auth_submit_x'])) {
            $try = self::auth($_POST['auth_login'], $_POST['auth_password']);

            if ($try) {
                $session->login($try->getId());
                $session->setParam(
                    App_Cms_Session::ACT_PARAM_NEXT,
                    App_Cms_Session::ACT_LOGIN
                );

            } else {
                $session->setParam(
                    App_Cms_Session::ACT_PARAM_NEXT,
                    App_Cms_Session::ACT_LOGIN_ERROR
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
                        App_Cms_Session::ACT_PARAM_NEXT,
                        App_Cms_Session::ACT_REMIND_PWD
                    );

                    $user->remindPassword();
                }

            } else {
                $session->setParam(
                    App_Cms_Session::ACT_PARAM_NEXT,
                    App_Cms_Session::ACT_REMIND_PWD_ERROR
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
                    App_Cms_Session::ACT_PARAM_NEXT,
                    $try && $try->changePassword() == 0 ? App_Cms_Session::ACT_CHANGE_PWD : App_Cms_Session::ACT_CHANGE_PWD_ERROR
                );

            } else {
                $session->setParam(
                    App_Cms_Session::ACT_PARAM_NEXT,
                    App_Cms_Session::ACT_LOGOUT
                );
            }

            reload();

        } else if (isset($_GET['e']) && $session->isLoggedIn()) {
            $session->logout();

            $session->setParam(
                App_Cms_Session::ACT_PARAM_NEXT,
                App_Cms_Session::ACT_LOGOUT
            );

            reload();

        } else {
            $session->setParam(
                App_Cms_Session::ACT_PARAM,
                $session->getParam(App_Cms_Session::ACT_PARAM_NEXT) ? $session->getParam(App_Cms_Session::ACT_PARAM_NEXT) : App_Cms_Session::ACT_START
            );

            $session->setParam(
                App_Cms_Session::ACT_PARAM_NEXT,
                App_Cms_Session::ACT_CONTINUE
            );

            self::$_siteUser = $session->isLoggedIn()
                             ? self::auth($session->getUserId())
                             : false;
        }
    }

    public static function checkUnique($_value, $_excludeId = null)
    {
        return self::isUnique('email', $_value, $_excludeId);
    }

    /**
     * @return App_Cms_User
     */
    public static function auth()
    {
        if (func_num_args() == 1) {
            $user = self::getById(func_get_arg(0));

        } else if (func_num_args() == 2) {
            $user = self::getBy(
                array('email', 'passwd'),
                array(func_get_arg(0), md5(func_get_arg(1)))
            );
        }

        return !empty($user) && $user->statusId == 1 ? $user : false;
    }

    public function remindPassword()
    {
        global $g_mail;

        if ($this->email) {
            $this->reminderDate = date('Y-m-d H:i:s');
            $this->reminderKey = App_Db::get()->getUnique(
                $this->getTable(),
                'reminder_key',
                30
            );

            $this->update();

            $message =
                'Для смены пароля к сайту http://' .
                $_SERVER['HTTP_HOST'] . ' загрузите страницу: http://' .
                $_SERVER['HTTP_HOST'] . '?r=' . $this->reminderKey . "\r\n\n" .
                'Если вы не просили поменять пароль, проигнорируйте это сообщение.';

            return send_email(
                $g_mail,
                $this->email,
                'Смена пароля',
                $message,
                null,
                false
            );
        }
    }

    public function changePassword()
    {
        global $g_mail;

        if ($this->email) {
            $date = $this->getDate('reminder_date');

            if (
                $this->statusId == 1 &&
                $date &&
                $date > time() - 60 * 60 * 24
            ) {
                $password = $this->generatePassword();

                $this->setPassword($password);
                $this->reminderKey = '';
                $this->reminderDate = '';
                $this->update();

                $message = 'Доступ к сайту http://' . $_SERVER['HTTP_HOST'] .
                           ".\r\n\nЛогин: {$this->email}\r\nПароль: $password";

                return send_email($g_mail, $this->email, 'Доступ', $message, null, false) ? 0 : 3;

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
        return Ext_String::getRandomReadableAlt(8);
    }

    public function setPassword($_password)
    {
        $this->passwd = md5($_password);
    }

    public function updatePassword($_password)
    {
        $this->updateAttr('passwd', md5($_password));
    }

    public function getBackOfficeXml($_xml = array(), $_attrs = array())
    {
        $attrs = $_attrs;

        if (!isset($attrs['is_published']) && $this->statusId == 1) {
            $attrs['is_published'] = 1;
        }

        return parent::getBackOfficeXml($_xml, $attrs);
    }
}
