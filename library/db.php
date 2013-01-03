<?php

abstract class Core_Db
{
    /**
     * @var Ext_Mysqli
     */
    private static $_db;

    /**
     * @return Ext_Mysqli
     */
    public static function get()
    {
        if (!isset(self::$_db)) {
            self::$_db = new Ext_Mysqli(DB_CONNECTION_STRING);
        }

        return self::$_db;
    }

    /**
     * @param string|integer|array $_data
     * @param string $_quote
     * @return string
     */
    public static function escape($_data, $_quote = null)
    {
        return self::get()->escape($_data, $_quote);
    }

    /**
     * @param string $_where Path to future dump file.
     * @return string
     */
    public static function dump($_where)
    {
        $user = self::get()->getUser();
        $password = self::get()->getPassword();
        $host = self::get()->getHost();
        $database = self::get()->getDatabase();

        $connectionParams = "-u$user -p$password -h$host";
        if (self::get()->getPort()) {
            $connectionParams .= ' -P' . self::get()->getPort();
        }

        return exec("mysqldump $connectionParams $database > $_where");
    }
}
