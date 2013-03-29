<?php

/**
 * Функции для отслеживания рекламных переходов с других сайтов.
 */
abstract class Core_Cms_Front_Adv
{
    public static function getParams()
    {
        return array('utm_source', 'adv');
    }

    public static function getMailParams()
    {
        $result = array();
        $params = static::getParams();
        $params[] = 'HTTP_REFERER';

        foreach ($params as $item) {
            $value = static::getCookie($item);

            if (!empty($value)) {
                $result[$item] = $value;
            }
        }

        return $result;
    }

    public static function monitor()
    {
        global $gHost;

        foreach (static::getParams() as $item) {
            if (!empty($_GET[$item])) {
                static::setCookie($item, $_GET[$item]);
            }
        }

        $envName = 'HTTP_REFERER';

        if (!empty($_SERVER[$envName])) {
            $referer = strtolower($_SERVER[$envName]);
            $url = Ext_File::parseUrl($referer);

            if (!empty($url['host']) && $url['host'] != strtolower($gHost)) {
                $prev = static::getCookie($envName);

                if (!$prev || $prev != $referer) {
                    static::setCookie($envName, $referer);
                }
            }
        }
    }

    public static function setCookie($_name, $_value)
    {
        global $gEnv;

        $name = 'adv_' . strtolower($_name);
        $_COOKIE[$name] = $_value;

        $host = $gEnv == 'production' && !empty($_SERVER['HTTP_HOST'])
              ? '.' . $_SERVER['HTTP_HOST']
              : null;

        setcookie($name, $_value, 0, '/', $host);
    }

    public static function getCookie($_name)
    {
        $name = 'adv_' . strtolower($_name);
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
    }
}
