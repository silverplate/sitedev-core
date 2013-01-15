<?php

abstract class Core_Cms_Back_User extends App_Model
{
    protected $_linkParams = array(
        'sections' => 'App_Cms_Back_User_Has_Section'
    );

    /**
     * @var App_Cms_Back_User|false
     */
    protected static $_current;

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('string')->setLength(10);
        $this->addAttr('status_id', 'integer');
        $this->addAttr('title', 'string');
        $this->addAttr('login', 'string');
        $this->addAttr('passwd', 'string');
        $this->addAttr('email', 'string');
        $this->addAttr('ip_restriction', 'string');
        $this->addAttr('reminder_key', 'string');
        $this->addAttr('reminder_time', 'integer');
    }

    public function checkUnique()
    {
        return self::isUnique(
            'login',
            $this->login,
            $this->id ? $this->id : null
        );
    }

    /**
     * @return App_Cms_Back_User
     */
    public static function auth()
    {
        if (func_num_args() == 1) {
            $user = self::getById(func_get_arg(0));

        } else if (func_num_args() == 2) {
            $user = self::getBy(
                array('login', 'passwd'),
                array(func_get_arg(0), md5(func_get_arg(1)))
            );
        }

        return !empty($user) &&
               $user->statusId == 1 &&
               (!$user->ipRestriction || in_array($_SERVER['REMOTE_ADDR'], Ext_String::split($user->ipRestriction)))
             ? $user
             : false;
    }

    public function getSections($_isPublished = true)
    {
        return App_Cms_Back_Section::getList(array(
            'is_published' => $_isPublished,
            App_Cms_Back_Section::getPri() => $this->getLinkIds('sections', $_isPublished)
        ));
    }

    public function isSection($_id)
    {
        return in_array($_id, $this->getLinkIds('sections'));
    }

    public function remindPassword()
    {
        if ($this->email) {
            $this->reminderKey = Ext_Db::get()->getUnique(self::getTbl(), 'reminder_key');
            $this->reminderTime = time();
            $this->update();

            $message = 'Для смены пароля к системе управления сайта http://' .
                       $_SERVER['HTTP_HOST'] . App_Cms_Back_Office::$uriStartsWith .
                       ' загрузите страницу http://' .
                       $_SERVER['HTTP_HOST'] . App_Cms_Back_Office::$uriStartsWith .
                       "?r={$this->reminderKey}\n\n" .
                       'Если вы не просили поменять пароль, проигнорируйте это сообщение.';

            return App_Cms_Back_Mail::forcePost(
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

                $message = 'Доступ к системе управления сайта http://' .
                           $_SERVER['HTTP_HOST'] .
                           App_Cms_Back_Office::$uriStartsWith .
                           ".\n\n" .
                           'Логин: ' . $this->login .
                           "\nПароль: $password";

                $ips = Ext_String::split($this->ipRestriction);

                if ($ips) {
                    $message .= "\nРазрешённы" .
                                (count($ips) > 1 ? 'е IP-адреса' : 'й IP-адрес') .
                                ': ' . implode(', ', $ips);
                }

                return App_Cms_Back_Mail::forcePost($this->email, $message, 'Доступ')
                     ? 0
                     : 3;
            }

            return 2;
        }

        return 1;
    }

    public function getTitle()
    {
        return $this->title ? $this->title : $this->login;
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

    public static function get()
    {
        if (!isset(self::$_current)) {
            self::$_current = App_Cms_Session::get()->isLoggedIn()
                            ? self::auth(App_Cms_Session::get()->getUserId())
                            : false;
        }

        return self::$_current;
    }
}
