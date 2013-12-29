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
        global $gHost,
               $gSiteKey,
               $gIsUsers,
               $gSiteLangType,
               $gSiteLang,
               $gIsKey,
               $gIsAdminMode,
               $gIsHidden;

        // Language

        $siteLangType = null;
        $siteLang = null;

        if (self::getLanguages()) {
            $url = empty($_SERVER['REQUEST_URI']) ? false : \Ext\File::parseUrl();

            if ($gHost && $url) {
                $langPath = array();

                foreach (self::getLanguages() as $lang => $params) {
                    foreach (\Ext\String::split($params[0]) as $item) {
                        if (
                            $gHost == $item ||
                            ('/' == $item && '/' == $url['path']) ||
                            ('/' != $item && strpos($url['path'], $item) === 0)
                        ) {
                            $localLangPath = explode('/', trim($item, '/'));

                            if (count($langPath) < count($localLangPath)) {
                                $siteLang = $lang;
                                $langPath = $localLangPath;
                                $siteLangType = $gHost == $item ? 'host' : 'path';
                            }
                        }
                    }
                }
            }
        }

        $gSiteLangType = $siteLangType;
        $gSiteLang = $siteLang
                   ? $siteLang
                   : self::getLanguages() ? key(self::getLanguages()) : '';


        // Administration

        $gIsKey = !empty($_GET['key']) && $_GET['key'] == $gSiteKey;
        $gIsAdminMode = self::getAdminParam('is_admin_mode');

        if ($gIsKey) {
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

        } else if ($gIsAdminMode) {
            self::setAdminParam('is_delete_cache', key_exists('delete_cache', $_GET));
        }

        $gIsHidden = $gIsAdminMode && self::getAdminParam('is_hidden');


        // Authorization

        if (!empty($gIsUsers)) {
            App_Cms_User::startSession();
        }
    }
}
