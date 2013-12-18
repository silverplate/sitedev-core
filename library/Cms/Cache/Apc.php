<?php

class Core_Cms_Cache_Apc
{
    protected $_ttl = 3600;

    /**
     * @var boolean
     */
    protected static $_isEnabled;

    /**
     * @var Core_Cms_Cache_Apc
     */
    protected static $_instance;

    /**
     * @return boolean
     */
    public static function isEnabled()
    {
        global $gIsApc;

        if (!isset(static::$_isEnabled)) {
            static::$_isEnabled = !empty($gIsApc) && extension_loaded('apc');
        }

        return self::$_isEnabled;
    }

    /**
     * @return Core_Cms_Cache_Apc
     */
    public static function instance()
    {
        if (!isset(self::$_instance)) {
            $class = get_called_class();
            self::$_instance = new $class;
        }

        return self::$_instance;
    }

    public function get($_key)
    {
        $result = null;
        $data = apc_fetch($_key, $result);

        return $result ? $data : null;
    }

    public function set($_key, $_data, $_ttl = null)
    {
        return apc_store(
            $_key,
            $_data,
            is_null($_ttl) ? $this->_ttl : $_ttl
        );
    }

    public function delete($_key)
    {
        return apc_delete($_key);
    }

    public function hasKey($_key)
    {
        return self::get($_key) !== null;
    }
}
