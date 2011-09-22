<?php

class Document extends ActiveRecord {
	private static $Base;
	private $IsChildren;
	private $Handler;
	private $_template;
	private $Language;
	protected $Links = array('navigations' => null);

	const TABLE = 'fo_document';

//	protected $Files;
//	protected $Images;
	protected $files;
	protected $images;

	public function GetFilePath() {
		return rtrim(DOCUMENT_ROOT . 'f/' . ltrim($this->GetAttribute('uri'), '/'), '/') . '/';
	}

	public function UploadFile($_name, $_tmp_name) {
		if ($_name && $_tmp_name) {
			$name = translit($_name);
			create_directory($this->GetFilePath(), true);
			move_uploaded_file($_tmp_name, $this->GetFilePath() . $name);
			chmod($this->GetFilePath() . $name, 0777);
		}
	}

	public function GetXml($_type, $_node_name = null, $_append_xml = null, $_append_attributes = null) {
		$node_name = ($_node_name) ? $_node_name : strtolower(__CLASS__);
		$result = '';

		switch ($_type) {
			case 'bo_list':
				$result .= '<' . $node_name . ' id="' . $this->GetId() . '"';
				if ($this->GetAttribute('is_published') == 1) $result .= ' is_published="true"';
				if (IS_USERS && $this->GetAttribute('auth_status_id') != User::AUTH_GROUP_ALL && User::GetAuthGroupTitle($this->GetAttribute('auth_status_id'))) {
					$result .= ' prefix="' . strtolower_utf8(substr_utf8(User::GetAuthGroupTitle($this->GetAttribute('auth_status_id')), 0, 1)) . '"';
				}

				if ($_append_attributes) {
					foreach ($_append_attributes as $name => $value) {
						$result .= ' ' . $name . '="' . $value . '"';
					}
				}

				$result .=
					'><title><![CDATA[' .
					($this->GetAttribute('title_compact')
						? $this->GetAttribute('title_compact')
						: $this->GetTitle()) .
					']]></title>';

				$result .= $_append_xml;
				$result .= '</' . $node_name . '>';
				break;

			case 'list':
				$result .= '<' . $node_name . ' id="' . $this->GetId() . '"';
				if ($this->GetAttribute('is_published') == 1) $result .= ' is_published="true"';

				if ($_append_attributes) {
					foreach ($_append_attributes as $name => $value) {
						$result .= ' ' . $name . '="' . $value . '"';
					}
				}

				$result .= '><title><![CDATA[' . $this->GetTitle() . ']]></title>';

				if ($this->GetAttribute('title_compact')) {
					$result .= '<title_compact><![CDATA[' . $this->GetAttribute('title_compact') . ']]></title_compact>';
				}

				$result .= $_append_xml;
				$result .= '</' . $node_name . '>';
				break;
		}

		return $result;
	}

	public function GetLang() {
		global $g_langs;

		if (is_null($this->Language)) {
			$this->Language = '';
			if (isset($g_langs) && $g_langs) {
				foreach (array_keys($g_langs) as $i) {
					$pos = strpos($this->GetAttribute('uri'), '/' . $i . '/');
					if ($pos !== false && $pos == 0) {
						$this->Language = $i;
						break;
					}
				}
			}
		}

		return $this->Language;
	}

	public function GetUrl() {
		global $g_langs;

		if ($this->GetLang()) {
			return 'http://' . $g_langs[$this->GetLang()][0] . $this->GetUri();
		} else {
			return $this->GetUri();
		}
	}

	public function GetUri() {
		if ($this->GetLang()) {
			return substr($this->GetAttribute('uri'), strlen($this->GetLang()) + 2 - 1);
		} else {
			return $this->GetAttribute('uri');
		}
	}

	private function computeUri($_parentUri = null)
	{
		if (!is_null($_parentUri)) {
			$uri = $_parentUri;

		} else if (
		    $this->getAttribute('parent_id') &&
		    $this->getAttribute('parent_id') !== 'NULL'
		) {
			$parent = self::load($this->getAttribute('parent_id'));
			$uri = $parent->getAttribute('uri');
		}

		if (!isset($uri)) {
		    $uri = '/';
		}

        $folder = $this->getAttribute('folder');

		if ($folder != '/') {
			$uri .= $folder;

			if (strpos($folder, '.') === false) {
			    $uri .= '/';
			}
		}

		$this->setAttribute('uri', $uri);
	}

	public static function UpdateChildrenUri($_id = null) {
		$id = '';
		$uri = '';

		if (!is_null($_id)) {
			$obj = self::Load($_id);
			if ($obj) {
				$id = $_id;
				$uri = $obj->GetAttribute('uri');
			} else {
				return false;
			}
		}

		$list = self::getList(array('parent_id' => $id));
        foreach ($list as $item) {
            $folder = $item->getAttribute('folder');
            if ($folder != '/' && strpos($folder, '.') === false) {
                $folder .= '/';
            }

            $item->updateAttribute('uri', $uri . $folder);
            self::updateChildrenUri($item->getId());
        }
	}

	public function Create() {
		$this->ComputeUri();
		parent::Create();
	}

	public function Update() {
		$path = $this->GetFilePath();
		$this->ComputeUri();
		if ($path != $this->GetFilePath() && is_dir($path)) {
			move_directory($path, $this->GetFilePath());
		}

		parent::Update();
		self::UpdateChildrenUri($this->GetId());
	}

	public function Delete() {
		foreach (self::GetList(array('parent_id' => $this->GetId())) as $item) {
			$item->Delete();
		}

		foreach (DocumentData::GetList(array(self::GetPri() => $this->GetId())) as $item) {
			$item->Delete();
		}

		remove_directory($this->GetFilePath());
		parent::Delete();
	}

	public function IsChildren($_except_id = null) {
		if (is_null($this->IsChildren)) {
			$list = self::GetList(array('parent_id' => $this->GetId()));

			if (is_null($_except_id)) {
				$this->IsChildren = ($list);

			} elseif ($list) {
				$this->IsChildren = false;

				foreach ($list as $item) {
					if ($item->GetId() != $_except_id) {
						$this->IsChildren = true;
						break;
					}
				}

			} else {
				$this->IsChildren = false;
			}
		}

		return $this->IsChildren;
	}

	public static function GetMultiAncestors($_ids) {
		$result = array();
		foreach ($_ids as $id) {
			if (!in_array($id, $result)) {
				$result = array_merge($result, self::GetAncestors($id));
			}
		}
		return $result;
	}

	public static function GetAncestors($_id) {
		$result = array();
		$entry = Db::Get()->GetEntry('SELECT ' . self::GetPri() . ', parent_id FROM ' . self::GetTbl() . ' WHERE ' . self::GetPri() . ' = ' . get_db_data($_id));
		if ($entry) {
			array_push($result, $entry[self::GetPri()]);
			if ($entry['parent_id']) $result = array_merge($result, self::GetAncestors($entry['parent_id']));
		}
		return $result;
	}

	public function GetHandler() {
		if (is_null($this->Handler)) {
			$this->Handler = $this->GetAttribute(Handler::GetPri())
				? Handler::Load($this->GetAttribute(Handler::GetPri()))
				: false;
		}

		return $this->Handler;
	}

	public function getTemplate()
	{
		if (is_null($this->_template)) {
		    $key = TemplateDb::getPri();
		    $id = $this->getAttribute($key);
		    $this->_template = $id ? Template::getById($id) : false;
		}

		return $this->_template;
	}

	public function GetHandlerFile() {
		return $this->GetHandler() ? $this->GetHandler()->GetFilename() : false;
	}

	public static function initHandler(Handler $_handler, &$_document) {
        require_once($_handler->getFilename());

        $class = $_handler->getClassName();
        if (class_exists($class)) {
            return new $class($_document);
        }

		return false;
	}

	public static function GetQueryConditions($_conditions = array()) {
		$self = array('table' => self::GetTbl(), 'pk' => self::GetPri());
		$self['pk_attr'] = $self['table'] . '.' . $self['pk'];

		$result = array('tables' => array($self['table']), 'row_conditions' => array());

		if (isset($_conditions['navigations'])) {
			if ($_conditions['navigations']) {
				array_push($result['tables'], DocumentToNavigation::GetTbl());
				array_push($result['row_conditions'], $self['pk_attr'] . ' = ' . DocumentToNavigation::GetTbl() . '.' . $self['pk']);
				array_push($result['row_conditions'], DocumentToNavigation::GetTbl() . '.' . DocumentNavigation::GetPri() . (is_array($_conditions['navigations']) ? ' IN (' . get_db_data($_conditions['navigations']) . ')' : ' = ' . get_db_data($_conditions['navigations'])));

				if (isset($_conditions['is_published'])) {
					array_push($result['tables'], DocumentNavigation::GetTbl());
					array_push($result['row_conditions'], DocumentToNavigation::GetTbl() . '.' . DocumentNavigation::GetPri() . ' = ' . DocumentNavigation::GetPri(true));
					array_push($result['row_conditions'], DocumentNavigation::GetTbl() . '.is_published = ' . get_db_data($_conditions['is_published']));
				}
			}
			unset($_conditions['navigations']);
		}

		if ($_conditions) {
			foreach ($_conditions as $attribute => $value) {
                if ($value === 'NULL') {
                    array_push(
                        $result['row_conditions'],
                        'ISNULL(' . $self['table'] . '.' . $attribute . ')'
                    );

                } else if (is_array($value)) {
                    array_push(
                        $result['row_conditions'],
                        $self['table'] . '.' . $attribute . ' IN (' . get_db_data($value) . ')'
                    );

                } else {
                    array_push(
                        $result['row_conditions'],
                        $self['table'] . '.' . $attribute . ' = ' . get_db_data($value)
                    );
                }
			}
		}

		return $result;
	}

	public static function GetList($_attributes = array(), $_parameters = array(), $_row_conditions = array()) {
		$conditions = self::GetQueryConditions($_attributes);

		if ($_row_conditions) {
			$conditions['row_conditions'] = array_merge($conditions['row_conditions'], $_row_conditions);
		}

		return parent::GetList(
			__CLASS__,
			$conditions['tables'],
			self::GetBase()->GetAttributes(true),
			null,
			$_parameters,
			$conditions['row_conditions']
		);
	}

	public static function CheckUnique($_parent_id, $_folder, $_except_id = null) {
		$row_conditions = array();
		if ($_except_id) array_push($row_conditions, self::GetPri() . ' != ' . get_db_data($_except_id));
		return !(self::GetList(array('parent_id' => $_parent_id, 'folder' => $_folder), array('count' => 1), $row_conditions));
	}

	public function GetLinks($_name, $_is_published = null) {
		if (!$this->Links[$_name]) {
			$conditions = array(self::GetPri() => $this->GetId());
			if (!is_null($_is_published)) $conditions['is_published'] = $_is_published;

			switch ($_name) {
				case 'navigations':
					$this->Links[$_name] = DocumentToNavigation::GetList($conditions);
					break;
			}
		}

		return $this->Links[$_name];
	}

	public function GetLinkIds($_name, $_is_published = null) {
		$result = array();

		switch ($_name) {
			case 'navigations':
				$keys = array(DocumentToNavigation::GetFirstKey(), DocumentToNavigation::GetSecondKey());
				break;
		}

		$key = self::GetPri() == $keys[0] ? $keys[1] : $keys[0];
		$links = $this->GetLinks($_name, $_is_published);

		if ($links) {
			foreach ($links as $item) {
				if ($item->GetAttribute($key)) {
					array_push($result, $item->GetAttribute($key));
				}
			}
		}

		return $result;
	}

	public function SetLinks($_name, $_value = null) {
		$this->Links[$_name] = array();

		switch ($_name) {
			case 'navigations':
				$class_name = 'DocumentToNavigation';
				$keys = array(DocumentToNavigation::GetFirstKey(), DocumentToNavigation::GetSecondKey());
				break;
		}

		if (is_array($_value)) {
			$key = $this->GetPri() == $keys[0] ? $keys[1] : $keys[0];

			foreach ($_value as $id => $item) {
				$obj = new $class_name;
				$obj->SetAttribute($this->GetPri(), $this->GetId());

				if (is_array($item)) {
					$obj->SetAttribute($key, $id);
					foreach ($item as $attribute => $value) {
						$obj->SetAttribute($attribute, $value);
					}

				} else {
					$obj->SetAttribute($key, $item);
				}

				array_push($this->Links[$_name], $obj);
			}
		}
	}

	public function __construct() {
		parent::__construct(self::GetTbl());
		foreach (self::GetBase()->Attributes as $item) {
			$this->Attributes[$item->GetName()] = clone($item);
		}
	}

	public static function GetBase() {
		if (!isset(self::$Base)) {
			self::$Base = new ActiveRecord(self::ComputeTblName());
			self::$Base->AddAttribute(self::ComputeTblName() . '_id', 'varchar', 30, true);
			self::$Base->AddForeignKey(Handler::GetBase());
			self::$Base->addForeignKey(TemplateDb::getBase());
			self::$Base->AddAttribute('parent_id', 'varchar', 30);
			self::$Base->AddAttribute('auth_status_id', 'int');
			self::$Base->AddAttribute('title', 'varchar', 255);
			self::$Base->AddAttribute('title_compact', 'varchar', 255);
			self::$Base->AddAttribute('folder', 'varchar', 255);
			self::$Base->AddAttribute('link', 'varchar', 255);
			self::$Base->AddAttribute('uri', 'varchar', 255);
			self::$Base->AddAttribute('is_published', 'boolean');
			self::$Base->AddAttribute('sort_order', 'int');
		}

		return self::$Base;
	}

	public static function GetPri($_is_table = false) {
		return self::GetBase()->GetPrimary($_is_table);
	}

	public static function GetTbl() {
		return self::GetBase()->GetTable();
	}

	public static function ComputeTblName()  {
		return DB_PREFIX . self::TABLE;
	}

	public static function Load($_value, $_attribute = null) {
		return parent::Load(__CLASS__, $_value, $_attribute);
	}
}

?>
