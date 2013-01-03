<?php

abstract class Core_Cms_Cache_Section
{
	private $Uri;
	private $Time;
	private $IsWhole;
	private $IsQueryImportant;

	public function __construct($_uri, $_time, $_is_whole = false, $_is_query = false) {
		$this->Uri = $_uri;
		$this->Time = (int) $_time;
		$this->IsWhole = ($_is_whole);
		$this->IsQueryImportant = ($_is_query);
	}

	public function GetUri() {
		return $this->Uri;
	}

	public function GetTime() {
		return (int) $this->Time;
	}

	public function IsWhole() {
		return $this->IsWhole;
	}

	public function IsQueryImportant() {
		return $this->IsQueryImportant;
	}
}
