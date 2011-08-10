<?php

class DocumentNavigation extends ActiveRecord {
	private static $Base;
	private static $NavItems = array();
	protected $Links = array('documents' => null);

	const TABLE = 'fo_navigation';

	public static function GetTypes() {
		return array(
			'list' => array('title' => 'Список'),
			'tree' => array('title' => 'Дерево')
		);
	}

    public static function getRowDocuments($_name)
    {
        global $g_langs;

		$list = Db::get()->getList('
			SELECT
			    d.' . Document::getPri() . ' AS id,
				d.*
			FROM
				' . DocumentNavigation::getTbl() . ' AS n,
				' . Document::getTbl() . ' AS d,
				' . DocumentToNavigation::getTbl() . ' AS l
			WHERE
			    n.is_published = 1 AND
				n.name = ' . get_db_data($_name) . ' AND
				n.' . DocumentNavigation::getPri() . ' = l.' . DocumentNavigation::getPri() . ' AND
				l.' . Document::getPri() . ' = d.' . Document::getPri() . ' AND
				d.is_published = 1' .
				(is_null(User::getAuthGroup()) ? '' : ' AND (d.auth_status_id = 0 OR d.auth_status_id & ' . User::getAuthGroup() . ')') . '
			ORDER BY
				d.sort_order
		');

		if ($list && !empty($g_langs)) {
			for ($i = 0; $i < count($list); $i++) {
				foreach (array_keys($g_langs) as $j) {
					$pos = strpos($list[$i]['uri'], '/' . $j . '/');
					if (0 === $pos) {
						$list[$i]['lang'] = $j;

						if (
							'host' == SITE_LANG_TYPE ||
							0 != strpos($list[$i]['uri'], '/' . $j . '/')
						) {
							$list[$i]['uri'] = substr($list[$i]['uri'], strlen($j) + 2 - 1);
						}

						break;
					}
				}
			}
		}

        return $list;
    }

    public static function getDocuments($_name)
    {
        $documents = array();
        $data = self::getRowDocuments($_name);

        foreach ($data as $row) {
            $document = new Document;
            $document->dataInit($row);
            $documents[$document->getId()] = $document;
        }

        return $documents;
    }

	public static function getNavigationXml($_name, $_type) {
		self::$NavItems = self::getRowDocuments($_name);

		$result = $_type == 'tree'
		        ? self::GetNavigationXmlTree()
		        : self::GetNavigationXmlList();

        return $result ? getNode($_name, $result) : false;
	}

	public function GetNavigationXmlTree($_parent_id = '') {
		$result = '';
		$keys = array_keys(self::$NavItems);
		foreach ($keys as $key) {
			if (isset(self::$NavItems[$key])) {
				$item = self::$NavItems[$key];
				if ($item['parent_id'] == $_parent_id) {
					unset(self::$NavItems[$key]);
					$result .= '<item uri="' . $item['uri'] . '" link="' . ($item['link'] ? $item['link'] : $item['uri']) . '"';
					if (isset($item['lang'])) $result .= ' xml:lang="' . $item['lang'] . '"';
					$result .= '><title><![CDATA[' . ($item['title_compact'] ? $item['title_compact'] : $item['title']) . ']]></title>';
					$result .= self::GetNavigationXmlTree($item['id']);
					$result .= '</item>';
				}
			}
		}
		return $result;
	}

	public function GetNavigationXmlList() {
		$result = '';

		foreach (self::$NavItems as $item) {
			$result .= '<item uri="' . $item['uri'] . '" link="' . ($item['link'] ? $item['link'] : $item['uri']) . '"';
			if (isset($item['lang'])) $result .= ' xml:lang="' . $item['lang'] . '"';
			$result .= '><title><![CDATA[' . ($item['title_compact'] ? $item['title_compact'] : $item['title']) . ']]></title>';
			$result .= '</item>';
		}

		return $result;
	}

	public function GetXml($_type, $_node_name = null, $_append_xml = null) {
		$node_name = ($_node_name) ? $_node_name : strtolower(__CLASS__);
		$result = '';

		switch ($_type) {
			case 'bo_list':
				$result .= '<' . $node_name . ' id="' . $this->GetId() .'"';
				if ($this->GetAttribute('is_published') == 1) $result .= ' is_published="true"';

				$result .= '><title><![CDATA[' . $this->GetTitle() . ']]></title>';
				$result .= $_append_xml;
				$result .= '</' . $node_name . '>';
				break;
		}

		return $result;
	}

	public function GetLinks($_name, $_is_published = null) {
		if (!$this->Links[$_name]) {
			$conditions = array(self::GetPri() => $this->GetId());
			if (!is_null($_is_published)) $conditions['is_published'] = $_is_published;

			switch ($_name) {
				case 'documents':
					$this->Links[$_name] = DocumentToNavigation::GetList($conditions);
					break;
			}
		}

		return $this->Links[$_name];
	}

	public function GetLinkIds($_name, $_is_published = null) {
		$result = array();

		switch ($_name) {
			case 'documents':
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
			case 'documents':
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
			self::$Base->AddAttribute('name', 'varchar', 255);
			self::$Base->AddAttribute('type', 'varchar', 255);
			self::$Base->AddAttribute('title', 'varchar', 255);
			self::$Base->AddAttribute('is_published', 'boolean');
			self::$Base->AddAttribute('sort_order', 'int', 11);
		}

		return self::$Base;
	}

	public static function GetPri($_is_table = false) {
		return self::GetBase()->GetPrimary($_is_table);
	}

	public static function GetTbl() {
		return self::GetBase()->GetTable();
	}

	public static function Load($_value, $_attribute = null) {
		return parent::Load(__CLASS__, $_value, $_attribute);
	}

	public static function GetList($_attributes = array(), $_parameters = array(), $_rowConditions = array()) {
		return parent::GetList(
			__CLASS__,
			self::GetTbl(),
			self::GetBase()->GetAttributes(),
			$_attributes,
			$_parameters,
			$_rowConditions
		);
	}

	public static function ComputeTblName()  {
		return DB_PREFIX . self::TABLE;
	}
}

?>
