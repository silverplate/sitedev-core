<?php

abstract class Core_Cms_Cache_Project extends Core_Cms_Cache
{
    public function __construct($_path = null, $_category = null, $_uri = null)
    {
        global $gIsCache;

        $path = is_null($_path) ? WD . 'cache/' : $_path;
        parent::__construct($path, $_category, $_uri);

        $this->QueryIgnore[] = 'OK';
        $this->QueryIgnore[] = 'xml';
        $this->QueryIgnore[] = 'key';

        $this->IsAble = !empty($gIsCache);
    }

    public function isAvailable()
    {
        return parent::isAvailable() &&
               !App_Cms_Front_Office::getAdminParam('is_admin_mode');
    }
}
