<?php

abstract class Core_Cms_Page
{
	protected $Title;
	protected $System = array();
	protected $SystemAttributes = array();
	protected $Content = array();
	protected $Template;
	protected $RootNodeName;
	protected $RootNodeAttributes = array();
	public $Url = array();

	public function __construct() {
		$this->ComputeUrl();
	}

	public function SetTitle($_value) {
		$this->Title = $_value;
	}

	public function GetTitle() {
		return $this->Title;
	}

	public function SetTemplate($_template_file) {
		if (is_file($_template_file)) {
			$this->Template = $_template_file;
			return true;
		} else {
			return false;
		}
	}

	public function SetRootNodeName($_node_name) {
		$this->RootNodeName = $_node_name;
	}

	public function GetRootNodeName() {
		return ($this->RootNodeName) ? $this->RootNodeName : 'page';
	}

	public function SetRootNodeAttribute($_name, $_value) {
		$this->RootNodeAttributes[$_name] = $_value;
	}

	protected function ComputeUrl() {
		$this->Url = parse_url($_SERVER['REQUEST_URI']);
		$this->Url['request_uri'] = $_SERVER['REQUEST_URI'];
		$this->Url['host'] = $_SERVER['HTTP_HOST'];
		if (!isset($this->Url['query'])) {
			$this->Url['query'] = '';
		}

		$replace = array('index.html', 'index.php');
		$this->Url['base_path'] = str_replace($replace, '', $this->Url['path']);
		$this->Url['base_url'] = str_replace($replace, '', $this->Url['request_uri']);
	}

	public function AddSystem($_source) {
		if ($_source) {
			$this->System[] = $_source;
		}
	}

	public function AddSystemAttribute($_name, $_value = 'true') {
		$this->SystemAttributes[$_name] = $_value;
	}

	public function AddContent($_source) {
		if ($_source) {
			$this->Content[] = $_source;
		}
	}

    public function getContent()
    {
        return $this->Content;
    }

    public function setContent(array $_content)
    {
        $this->Content = $_content;
    }

	public function Output() {
		if (isset($_GET['xml']) || !$this->Template) {
			header('Content-type: text/xml; charset=utf-8');

			echo Core_Cms_Ext_Xml::getDocumentForXml(
		        $this->getXml(),
		        $this->getRootNodeName()
			);

		} else {
			echo $this->getHtml();
		}
	}

	public function GetXml() {
		$result = '<' .  $this->GetRootNodeName();

		foreach ($this->RootNodeAttributes as $name => $value) {
			$result .= " {$name}=\"{$value}\"";
		}

		$result .= '>';

		if ($this->Content) {
			$result .= '<content>' . implode($this->Content) . '</content>';
		}

		if ($this->System) {
			$result .= '<system';
			foreach ($this->SystemAttributes as $name => $value) {
				$result .= " {$name}=\"{$value}\"";
			}
			$result .= '>' . implode($this->System) . '</system>';
		}

		if ($this->Title) {
			$result .= '<title><![CDATA[' . $this->Title . ']]></title>';
		}

		$result .= $this->getUrlXml();
		$result .= Ext_Date::getXml(time());
		$result .= '</' .  $this->GetRootNodeName() . '>';

		return $result;
	}

	public function getUrlXml() {
		$result = '<url';
		foreach (array_diff_key($this->Url, array('request_uri' => '')) as $name => $value) {
			if ($value != '') {
				$result .= ' ' . $name . '="' . str_replace('&', '&amp;', $value) . '"';
			}
		}
		return $result . '><![CDATA[' . $this->Url['request_uri'] . ']]></url>';
	}

	public function GetHtml() {
		if ($this->Template) {
			$obj = new XSLTProcessor;
			$obj->importStylesheet(Ext_Dom::load($this->Template));

			return $obj->transformToXml(Ext_Dom::get(Ext_Xml::getDocumentForXml(
			    $this->getXml(),
		        $this->getRootNodeName()
	        )));

		} else {
			throw new Exception ('Шаблон не указан.');
		}
	}
}

?>