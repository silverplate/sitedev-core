<?php

namespace Core\Cms\Back;

abstract class User extends \App\ActiveRecord
{
    protected $_linkParams = array(
        'sections' => '\App\Cms\Back\User\Has\Section'
    );

    /**
     * @var \App\Cms\Back\User|false
     */
    protected static $_current;

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('integer');
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
     * @return User
     */
    public static function auth()
    {
        if (func_num_args() == 1) {
            $user = self::getById(func_get_arg(0));

        } else if (func_num_args() == 2) {
            $user = self::getBy(
                array('login', 'passwd'),
                array(func_get_arg(0), self::cryptPassword(func_get_arg(1)))
            );
        }

        return !empty($user) &&
               $user->statusId == 1 &&
               (!$user->ipRestriction || in_array($_SERVER['REMOTE_ADDR'], \Ext\String::split($user->ipRestriction)))
             ? $user
             : false;
    }

    public function getSections($_isPublished = true)
    {
        return \App\Cms\Back\Section::getList(array(
            'is_published' => $_isPublished,
            \App\Cms\Back\Section::getPri() => $this->getLinkIds('sections', $_isPublished)
        ));
    }

    public function isSection($_id)
    {
        return in_array($_id, $this->getLinkIds('sections'));
    }

    public function remindPassword()
    {
        global $gHost;

        if ($this->email) {
            $this->reminderKey = \Ext\Db::get()->getUnique(self::getTbl(), 'reminder_key');
            $this->reminderTime = time();
            $this->update();

            $message = "Для смены пароля к системе управления сайта http://$gHost" .
                       \App\Cms\Back\Office::$uriStartsWith .
                       " загрузите страницу http://$gHost" .
                       \App\Cms\Back\Office::$uriStartsWith .
                       "?r={$this->reminderKey}\n\n" .
                       'Если вы не просили поменять пароль, проигнорируйте это сообщение.';

            return \App\Cms\Back\Mail::forcePost(
                $this->email,
                $message,
                'Смена пароля'
            );
        }
    }

    public function changePassword()
    {
        global $gHost;

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

                $message = "Доступ к системе управления сайта http://$gHost" .
                           \App\Cms\Back\Office::$uriStartsWith .
                           ".\n\n" .
                           'Логин: ' . $this->login .
                           "\nПароль: $password";

                $ips = \Ext\String::split($this->ipRestriction);

                if ($ips) {
                    $message .= "\nРазрешённы" .
                                (count($ips) > 1 ? 'е IP-адреса' : 'й IP-адрес') .
                                ': ' . implode(', ', $ips);
                }

                return \App\Cms\Back\Mail::forcePost($this->email, $message, 'Доступ')
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

    public static function get()
    {
        if (!isset(self::$_current)) {
            self::$_current = \App\Cms\Session::get()->isLoggedIn()
                            ? self::auth(\App\Cms\Session::get()->getUserId())
                            : false;
        }

        return self::$_current;
    }
}
