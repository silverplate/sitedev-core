<?php

abstract class Core_Cms_Document_Data extends App_ActiveRecord
{
	private static $Base;
	private $Controller;

	const TABLE = 'fo_data';

	public static function GetApplyTypes() {
		return array(1 => 'На&nbsp;эту страницу', 'На&nbsp;вложенные', 'На&nbsp;эту и&nbsp;вложенные');
	}

	public function CheckApplyType() {
		if (!in_array((int) $this->GetAttribute('apply_type_id'), array_keys(self::GetApplyTypes()))) {
			$this->SetAttribute('apply_type_id', 1);
		}
	}

	public function create() {
		$this->CheckApplyType();
		parent::create();
	}

	public function Update() {
		$this->CheckApplyType();
		parent::Update();
	}

	public function GetParsedContent($_content) {
		switch ($this->GetAttribute(App_Cms_Document_Data_ContentType::GetPri())) {
			case 'integer': return (int) $_content;
			case 'float':   return (float) $_content;
			default:        return Ext_Xml::decodeCdata($_content);
		}
	}

	public function GetTypeId() {
		return $this->GetAttribute(App_Cms_Document_Data_ContentType::GetPri());
	}

	public function SetTypeId($_type_id) {
		return $this->SetAttribute(App_Cms_Document_Data_ContentType::GetPri(), $_type_id);
	}

	public function getXml($_additional_xml = null) {
		$result = '<document_data id="' . $this->GetId() . '"';

		if ($this->GetTypeId()) {
			$result .= ' type_id="' . $this->GetTypeId() . '"';
		}

		if ($this->GetAttribute('tag')) {
			$result .= ' tag="' . $this->GetAttribute('tag') . '"';
		}

		if ($this->GetAttribute('is_published') == 1) {
			$result .= ' is_published="true"';
		}

		if ($this->GetAttribute('is_mount') == 1) {
			$result .= ' is_mount="true"';
		}

		$result .= '>';

		$result .= Ext_Xml::notEmptyCdata('title', $this->getTitle());

		if ($this->getController()) {
		    $result .= Ext_Xml::cdata('controller', $this->getController()->getTitle());
		}

		$result .= Ext_Xml::notEmptyCdata('content', $this->getAttribute('content'));

		if (IS_USERS && $this->GetAttribute('auth_status_id') != App_Cms_User::AUTH_GROUP_ALL && App_Cms_User::GetAuthGroupTitle($this->GetAttribute('auth_status_id'))) {
			$result .= Ext_Xml::cdata(
		        'auth-group',
		        App_Cms_User::GetAuthGroupTitle($this->GetAttribute('auth_status_id'))
	        );
		}

		if ($_additional_xml) {
			$result .= '<additional>' . $_additional_xml . '</additional>';
		}

		return $result . '</document_data>';
	}

	public function GetController() {
		if (is_null($this->Controller)) {
			$this->Controller = $this->GetAttribute(App_Cms_Controller::GetPri())
				? App_Cms_Controller::load($this->GetAttribute(App_Cms_Controller::GetPri()))
				: false;
		}

		return $this->Controller;
	}

	public function GetControllerFile() {
		return $this->GetController() ? $this->GetController()->GetFilename() : false;
	}

	public static function initController($_controller, &$_documentData, &$_document)
	{
        require_once $_controller->getFilename();

        $class = $_controller->getClassName();
        if (class_exists($class)) {
            return new $class($_documentData, $_document);
        }

		return false;
	}

	public function __construct() {
		parent::__construct(self::GetTbl());
		foreach (self::GetBase()->_attributes as $item) {
			$this->_attributes[$item->GetName()] = clone($item);
		}
	}

	public static function GetBase() {
		if (!isset(self::$Base)) {
			self::$Base = new App_ActiveRecord(self::ComputeTblName());
			self::$Base->AddAttribute(self::ComputeTblName() . '_id', 'varchar', 30, true);
			self::$Base->AddForeignKey(App_Cms_Document::GetBase());
			self::$Base->AddForeignKey(App_Cms_Controller::GetBase());
			self::$Base->AddForeignKey(App_Cms_Document_Data_ContentType::GetBase());
			self::$Base->AddAttribute('auth_status_id', 'int');
			self::$Base->AddAttribute('tag', 'varchar', 255);
			self::$Base->AddAttribute('title', 'varchar', 255);
			self::$Base->AddAttribute('content', 'text');
			self::$Base->AddAttribute('apply_type_id', 'int');
			self::$Base->AddAttribute('is_mount', 'boolean');
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

	public static function load($_value, $_attribute = null) {
		return parent::load(get_called_class(), $_value, $_attribute);
	}

	public static function getList($_attributes = array(), $_parameters = array(), $_row_conditions = array()) {
		return parent::getList(
			get_called_class(),
			self::GetTbl(),
			self::GetBase()->GetAttributes(),
			$_attributes,
			$_parameters,
			$_row_conditions
		);
	}

	public static function ComputeTblName()  {
		return DB_PREFIX . self::TABLE;
	}
}

?>
