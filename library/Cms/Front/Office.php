<?php

abstract class Core_Cms_Front_Office
{
    protected static $_adminParams = array();

    /**
     * @return array|false
     */
    public static function getLanguages()
    {
//         return array(
//             'ru' => array('/', 'Русский'),
//             'en' => array('/eng/', 'Английский')
//         );

        return false;
    }

    public static function getAdminParam($_name)
    {
        return !empty($_COOKIE[$_name]);
    }

    public static function setAdminParam($_name, $_isOn)
    {
        if ($_isOn) {
            setcookie($_name, 1, null, '/');
            $_COOKIE[$_name] = 1;

        } else {
            setcookie($_name, null, null, '/');
            unset($_COOKIE[$_name]);
        }
    }

    public static function bootstrap()
    {
        // Language

        $siteLangType = null;
        $siteLang = null;

        if (self::getLanguages()) {
            $host = empty($_SERVER['HTTP_HOST']) ? false : $_SERVER['HTTP_HOST'];
            $url = empty($_SERVER['REQUEST_URI']) ? false : parse_url($_SERVER['REQUEST_URI']);

            if ($host && $url) {
                $langPath = array();

                foreach (self::getLanguages() as $lang => $params) {
                    foreach (Ext_String::split($params[0]) as $item) {
                        if (
                            $host == $item ||
                            ('/' == $item && '/' == $url['path']) ||
                            ('/' != $item && strpos($url['path'], $item) === 0)
                        ) {
                            $localLangPath = explode('/', trim($item, '/'));

                            if (count($langPath) < count($localLangPath)) {
                                $siteLang = $lang;
                                $langPath = $localLangPath;
                                $siteLangType = $host == $item ? 'host' : 'path';
                            }
                        }
                    }
                }
            }
        }

        define('SITE_LANG_TYPE', $siteLangType);
        define('SITE_LANG', $siteLang ? $siteLang : self::getLanguages() ? key(self::getLanguages()) : '');


        // Administration

        define('IS_KEY', isset($_GET['key']) && $_GET['key'] == SITE_KEY);
        define('IS_ADMIN_MODE', self::getAdminParam('is_admin_mode'));

        if (IS_KEY) {
            self::setAdminParam('is_admin_mode', true);
            self::setAdminParam('is_hidden', true);

            $getParams = $_GET;
            unset($getParams['key']);

            if ($getParams) {
                $get = array();

                foreach ($getParams as $key => $value) {
                    $get[] = $key . ($value ? "=$value" : '');
                }

                reload('?' . implode('&', $get));

            } else {
                reload();
            }

        } else if (IS_ADMIN_MODE) {
            self::setAdminParam('is_delete_cache', key_exists('delete_cache', $_GET));
        }

        define('IS_HIDDEN', IS_ADMIN_MODE && self::getAdminParam('is_hidden'));


        // Authorization

        if (defined('IS_USERS') && IS_USERS) {
            App_Cms_User::startSession();
        }
    }
}
