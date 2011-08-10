<?php

function get_file_name($_file) {
	$info = pathinfo($_file);
	return isset($info['extension']) && $info['extension']
		? substr($info['basename'], 0, strlen($info['basename']) - strlen($info['extension']) - 1)
		: $info['basename'];
}

function get_file_extension($_file) {
	$info = pathinfo($_file);
	return isset($info['extension']) ? strtolower($info['extension']) : '';
}

function create_directory($_dir, $_is_recursive = false) {
	if (!is_dir($_dir)) {
		$mask = umask(0);
		mkdir($_dir, 0777, $_is_recursive);
		umask($mask);
	}
}

function remove_file($_file) {
	return is_file($_file) ? unlink($_file) : false;
}

function remove_directory($_dir, $_is_self_delete = true, $_is_only_files = false) {
	$dir = rtrim($_dir, '/') . '/';
	if (is_dir($dir)) {
		$dir_handle = opendir($dir);
		while ($item = readdir($dir_handle)) {
			if ($item != '.' && $item != '..') {
				if (is_dir($dir . '/' . $item)) {
					if (!$_is_only_files) remove_directory($dir . '/' . $item);
				} else {
					unlink($dir . '/' . $item);
				}
			}
		}
		if ($_is_self_delete) rmdir($dir);
		closedir($dir_handle);
	}
}

function empty_directory($_dir, $_is_only_files = false) {
	remove_directory($_dir, false, $_is_only_files);
}

function is_directory_empty($_dir, $_ignore = null) {
	if (is_dir($_dir)) {
		$dir_handle = opendir($_dir);
		$ignore = (is_null($_ignore)) ? array() : explode(' ', strtolower($_ignore));
		while ($item = readdir($dir_handle)) {
			if ($item != '.' && $item != '..' && (!$ignore || !in_array(strtolower($item), $ignore))) {
				return false;
			}
		}
		closedir($dir_handle);
		return true;
	}
	return false;
}

function move_directory($_from, $_to) {
	$from = rtrim($_from, '/') . '/';
	$to = rtrim($_to, '/') . '/';

	if (is_dir($from)) {
		create_directory($to, true);

		$dir_handle = opendir($from);
		while ($item = readdir($dir_handle)) {
			if ($item != '.' && $item != '..' && $to != $from . $item . '/') {
				if (is_dir($from . $item)) {
					move_directory($from . $item, $to . $item);
					remove_directory($from . $item);
				} else {
					rename($from . $item, $to . $item);
				}
			}
		}
		closedir($dir_handle);
		if (is_directory_empty($from)) rmdir($from);
	}
}

function get_file_by_name($_dir, $_name) {
	if (is_dir($_dir)) {
		$dir = rtrim($_dir, '/') . '/';
		foreach (array('.*', '*') as $try) {
			$search = glob($dir . $_name . $try);
			if ($search) return $search[0];
		}
	}
	return false;
}

function write_log($_log_file, $_label) {
	write_file($_log_file, implode("\t", array(date('Y-m-d H:i:s'), $_label)) . "\n", 'append');
}

function write_file($_file, $_content, $_mode = null) {
	file_put_contents(
		$_file,
		$_content,
		!empty($_mode) && strpos($_mode, 'append') !== false ? FILE_APPEND : null
	);
	@chmod($_file, 0777);
}

function get_max_upload_size() {
	return (int) ini_get('upload_max_filesize');
}

function get_size_measure($_size) {
	$result = array();

	if ($_size / (1024 * 1024) > 0.5) {
		$result['value'] = $_size / (1024 * 1024);
		$result['measure'] = 'МБ';
		$result['measure_en'] = 'MB';

	} elseif ($_size / 1024 > 0.5) {
		$result['value'] = $_size / 1024;
		$result['measure'] = 'КБ';
		$result['measure_en'] = 'KB';

	} else {
		$result['value'] = $_size;
		$result['measure'] = 'байт';
		$result['measure_en'] = 'bite';
	}

	$result['value'] = format_number($result['value']);
	$result['string'] = $result['value'] . '&nbsp;' . $result['measure'];
	$result['string_en'] = $result['value'] . '&nbsp;' . $result['measure_en'];

	return $result;
}

function goToUrl($_url) {
	header("Location: $_url");
	exit;
}

function reload($_append = null) {
	goToUrl('./' . $_append);
}

function documentNotFound() {
	header('HTTP/1.0 404 Not Found');

	if (class_exists('Document')) {
		$real_url = parse_url($_SERVER['REQUEST_URI']);
		$document = Document::Load(get_lang_inner_uri() . 'not_found/', 'uri');
		if ($document) {
			if ($document->GetAttribute('link') && $document->GetAttribute('link') != $real_url['path']) {
				goToUrl($document->GetAttribute('link'));

			} elseif ($document->GetHandler() && ($document->GetAttribute('is_published') == 1 || IS_SHOW_HIDDEN)) {
				$handler = Document::initHandler($document->getHandler(), $document);
				$handler->Execute();
				$handler->Output();
				exit();
			}
		}
	}

	echo '<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1>';
	echo '<p>The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found on this server.</p><hr />';
	echo '<i>' . $_SERVER['SERVER_SOFTWARE'] . ' at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] . '</i>';
	echo '</body></html>';
	exit();
}


class File {
	private $Path;
	private $Uri;
	private $FileName;
	private $Name;
	private $Extension;

	private $PathStartsWith;
	private $UriStartsWith;

	public function __construct($_path, $_path_starts_with = null, $_uri_starts_with = null) {
		$this->SetPathStartsWith($_path_starts_with);
		$this->SetUriStartsWith($_uri_starts_with);
		$this->SetPath($_path);
	}

	public function SetPathStartsWith($_path) {
		$this->PathStartsWith = $_path;
	}

	public function GetPathStartsWith() {
		return $this->PathStartsWith;
	}

	public function ComputeUri() {
		$this->Uri = ($this->GetPathStartsWith())
			? str_replace($this->GetPathStartsWith(), $this->GetUriStartsWith(), $this->GetPath())
			: $this->GetPath();
	}

	public function SetUriStartsWith($_uri) {
		$this->UriStartsWith = $_uri;
	}

	public function GetUriStartsWith() {
		return $this->UriStartsWith;
	}

	public function SetPath($_path) {
		$info = pathinfo($_path);
		if (!isset($info['extension'])) $info['extension'] = '';

		$this->Path = $_path;
		$this->FileName = $info['basename'];
		$this->Name = substr($info['basename'], 0, strlen($info['basename']) - strlen($info['extension']) - 1);
		$this->Extension = $info['extension'];

		$this->ComputeUri();
	}

	public function Delete() {
		if (is_file($this->GetPath())) {
			unlink($this->GetPath());
		}
	}

	private function PrepareAttribute($_value) {
		return str_replace('&', '&amp;', str_replace('&amp;', '&', $_value));
	}

	public function GetXml() {
		$size = get_size_measure(filesize($this->GetPath()));

		$result = '<file uri="' . $this->PrepareAttribute($this->GetUri()) . '" path="' . $this->PrepareAttribute($this->GetPath()) . '" filename="' . $this->PrepareAttribute($this->GetFileName()) . '" name="' . $this->PrepareAttribute($this->GetName()) . '" extension="' . $this->GetExtension() . '">';
		$result .= '<size xml:lang="ru" value="' . $size['value'] . '" measure="' . $size['measure'] . '"><![CDATA[' . $size['string'] . ']]></size>';
		$result .= '<size xml:lang="en" value="' . $size['value'] . '" measure="' . $size['measure_en'] . '"><![CDATA[' . $size['string_en'] . ']]></size>';
		$result .= '</file>';

		return $result;
	}

	/**
	 * @param DOMDocument $dom
	 * @param string $_type_attribute
	 * @return DOMElement
	 */
	public function GetNode(DOMDocument $dom) {
		$size = get_size_measure(filesize($this->GetPath()));
		$node = $dom->createElement('file');

		$node->setAttribute('uri', $this->GetUri());
		$node->setAttribute('path', $this->GetPath());
		$node->setAttribute('filename', $this->GetFileName());
		$node->setAttribute('name', $this->GetName());
		$node->setAttribute('extension', $this->GetExtension());

		$size_ru = $dom->createElement('size');
		$size_ru->setAttribute('xml:lang', 'ru');
		$size_ru->setAttribute('value', $size['value']);
		$size_ru->setAttribute('measure', $size['measure']);
		$size_ru->appendChild($dom->createCDATASection($size['string']));
		$node->appendChild($size_ru);

		$size_en = $dom->createElement('size');
		$size_en->setAttribute('xml:lang', 'en');
		$size_en->setAttribute('value', $size['value']);
		$size_en->setAttribute('measure', $size['measure_en']);
		$size_en->appendChild($dom->createCDATASection($size['string_en']));
		$node->appendChild($size_en);

		return $node;
	}

	public function GetPath() {
		return $this->Path;
	}

	public function GetUri() {
		return $this->Uri;
	}

	public function GetFileName() {
		return $this->FileName;
	}

	public function GetName() {
		return $this->Name;
	}

	public function GetExtension() {
		return $this->Extension;
	}

	public function GetSize() {
		return filesize($this->GetPath());
	}

	public static function NormalizeName($_name) {
		return str_replace(' ', '_', strtolower(translit($_name)));
	}
}

?>
