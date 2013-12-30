<?php

namespace Core\Cms\Cache;

abstract class Project extends \Core\Cms\Cache
{
    public function __construct($_path = null, $_category = null, $_uri = null)
    {
        global $gIsCache;

        $path = is_null($_path) ? WD . 'cache/' : $_path;
        parent::__construct($path, $_category, $_uri);

        $this->_queryIgnore[] = 'xml';
        $this->_queryIgnore[] = 'key';

        $this->_isAble = !empty($gIsCache);
    }

    public function isAvailable()
    {
        return parent::isAvailable() &&
               !\App\Cms\Front\Office::getAdminParam('is_admin_mode');
    }
}
