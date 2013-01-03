<?php

abstract class Core_Cms_Cache_Project extends Core_Cms_Cache
{
	public function __construct($_path = null, $_category = null, $_uri = null)
	{
		$path = is_null($_path) ? WD . 'cache/' : $_path;
		parent::__construct($path, $_category, $_uri);

		$this->QueryIgnore[] = 'OK';
		$this->QueryIgnore[] = 'xml';
		$this->QueryIgnore[] = 'key';

		$this->IsAble = IS_CACHE;
	}

	public function IsAvailable()
	{
		return parent::IsAvailable() && !get_admin_param('mode');
	}
}
