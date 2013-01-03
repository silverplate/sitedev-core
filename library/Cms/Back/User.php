<?php

abstract class Core_Cms_Back_User extends App_Model
{
    protected $_linkParams = array(
        'sections' => 'App_Cms_Back_User_Has_Section'
    );

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('string');
        $this->addAttr('status_id', 'integer');
        $this->addAttr('title', 'string');
        $this->addAttr('login', 'string');
        $this->addAttr('passwd', 'string');
        $this->addAttr('email', 'string');
        $this->addAttr('ip_restriction', 'string');
        $this->addAttr('reminder_key', 'string');
        $this->addAttr('reminder_date', 'datetime');
    }

    public static function checkUnique($_value, $_excludeId = null)
    {
        return self::isUnique('login', $_value, $_excludeId);
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
        global $g_section_start_url, $g_bo_mail;

        if ($this->email) {
            $this->reminderKey = App_Db::get()->getUnique(self::getTbl(), 'reminder_key');
            $this->reminderDate = date('Y-m-d H:i:s');
            $this->update();

            $message = 'Для смены пароля к системе управления сайта http://' .
                       $_SERVER['HTTP_HOST'] . "$g_section_start_url загрузите страницу: http://" .
                       $_SERVER['HTTP_HOST'] . "$g_section_start_url?r={$this->reminderKey}\r\n\n" .
                       'Если вы не просили поменять пароль, проигнорируйте это сообщение.';

            return send_email($g_bo_mail, $this->email, 'Смена пароля', $message, null, false);
        }
    }

    public function changePassword()
    {
        global $g_section_start_url, $g_bo_mail;

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

                $message = 'Доступ к системе управления сайта http://' . $_SERVER['HTTP_HOST'] .
                           "$g_section_start_url.\r\n\n" .
                           'Логин: ' . $this->login .
                           "\r\nПароль: $password";

                $ips = Ext_String::split($this->ipRestriction);

                if ($ips) {
                    $message .= "\r\nРазрешённы" .
                                (count($ips) > 1 ? 'е IP-адреса' : 'й IP-адрес') .
                                ': ' . implode(', ', $ips);
                }

                return send_email($g_bo_mail, $this->email, 'Доступ', $message, null, false) ? 0 : 3;

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
}
