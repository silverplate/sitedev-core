<?php

abstract class Core_Cms_Cache
{
	protected $IsAble;
	protected $IsQueryImportant;
	protected $Sections = array();
	protected $Time;
	protected $Path;
	protected $Category;
	protected $QueryIgnore;
	private $Uri;
	private $File;
	private $Section;
	private $SectionTime;
	private $IsSectionQueryImportant;

	public function __construct($_path, $_category = null, $_uri = null) {
		$this->IsAble = true;
		$this->IsQueryImportant = false;
		$this->Time = 30;
		$this->Path = rtrim($_path, '/') . '/';
		$this->Category = $_category;
		$this->QueryIgnore = array('delete_cache', 'no_cache');
		$this->GetUri($_uri);

		if (isset($_GET['delete_cache'])) {
			$this->DeletePage();
		}
	}

	public function GetUri($_uri = null) {
		if (is_null($this->Uri)) {
			$this->Uri = parse_url(is_null($_uri) ? $_SERVER['REQUEST_URI'] : $_uri);
			$this->Uri['path_info'] = pathinfo($this->Uri['path']);

			// $this->Uri['query'] = isset($this->Uri['query']) && $this->Uri['query'] && $this->QueryIgnore
			// 	? preg_replace('/(\?|&)(' . implode('|', $this->QueryIgnore) . ')(=[^&]?)?/', '', $this->Uri['query'])
			// 	: '';

			if (!empty($this->Uri['query']) && $this->QueryIgnore) {
				$query = '';
				foreach (explode('&', $this->Uri['query']) as $item) {
					$pair = explode('=', $item);
					if (!in_array($pair[0], $this->QueryIgnore)) {
						$query .= ('' == $query ? '' : '&') . $item;
					}
				}
				$this->Uri['query'] = $query;
			}
		}

		return $this->Uri;
	}

	public function GetRequestPath() {
		$request = $this->GetUri();
		return $request['path'];
	}

	public function GetRequestQuery() {
		$request = $this->GetUri();
		return $request['query'];
	}

	public function SetSection(Core_Cms_Cache_Section &$_obj) {
		$this->Sections[$_obj->GetUri()] = $_obj;
	}

	public function GetSection() {
		if (is_null($this->Section)) {
			if ($this->Sections) {
				if (isset($this->Sections[$this->GetRequestPath()])) {
					$this->Section = $this->Sections[$this->GetRequestPath()];

				} else {
					foreach ($this->Sections as $item) {
						if ($item->IsWhole() && strpos($this->GetRequestPath(), $item->GetUri()) === 0) {
							$this->Section = $item;
						}
					}
				}
			}

			if (is_null($this->Section)) {
				$this->Section = false;
			}
		}

		return $this->Section;
	}

	public function GetSectionTime() {
		if (is_null($this->SectionTime)) {
			$this->SectionTime = $this->GetSection() ? $this->GetSection()->GetTime() : $this->Time;
		}

		return $this->SectionTime;
	}

	public function GetSectionQueryImportant() {
		if (is_null($this->IsSectionQueryImportant)) {
			$this->IsSectionQueryImportant = $this->GetSection() ? $this->GetSection()->IsQueryImportant() : $this->IsQueryImportant;
		}

		return $this->IsSectionQueryImportant;
	}

	public function IsAvailable() {
		return ($this->IsAble && !$_POST && !array_intersect(array_keys($_GET), $this->QueryIgnore) && $this->GetSectionTime());
	}

	public function GetFile() {
		if (is_null($this->File)) {
			$this->File = $this->Path;
			if ($this->Category) $this->File .= 'g_' . $this->Category . '/';
			$path = pathinfo($this->GetRequestPath());

			if (isset($path['basename']) && $path['basename'] == 'index.html') {
				$this->File .= $path['dirname'] . '/';

			} else if (isset($path['basename']) && isset($path['extension'])) {
				$this->File .= $path['dirname'] . '/' . Ext_File::computeName($path['basename']) . '/';

			} else {
				$this->File .= $this->GetRequestPath();
			}

			$query = $this->GetSectionQueryImportant() && $this->GetRequestQuery()
				? str_replace(array('&', '=', '[', ']', '"', '\''), '_', Ext_String::translit(urldecode($this->GetRequestQuery())))
				: false;

			$this->File = str_replace('//', '/', $this->File) . ($query && $this->GetSectionQueryImportant()
				? '_q_' . $query . '.html'
				: 'index.html'
			);
		}

		return $this->File;
	}

	public function IsCache() {
		return (is_file($this->GetFile()) && mktime() - filemtime($this->GetFile()) < $this->GetSectionTime() * 60);
	}

	public function __toString() {
		return self::IsCache() ? file_get_contents($this->GetFile()) : false;
	}

	public function set($_content)
	{
	    Ext_File::createDir(dirname($this->getFile()));
	    Ext_File::write($this->getFile(), $_content);
	}

	function DeletePage() {
		if (is_file($this->GetFile())) {
			unlink($this->GetFile());
			$path = dirname($this->GetFile());

		    if (Ext_File::isDirEmpty($path)) {
			    Ext_File::deleteDir($path);
			}
		}
	}

	public function emptyPage()
	{
	    return Ext_File::deleteDir(dirname($this->getFile()), false, true);
	}

	public function emptyCache()
	{
		return Ext_File::deleteDir($this->Path, false);
	}
}
