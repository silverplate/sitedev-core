<?php

abstract class Core_Form_Button
{
    protected $Name;
    protected $Label;
    protected $ImageUrl;

    public function __construct($_name, $_label, $_image_url = null) {
        $this->Name = $_name;
        $this->Label = $_label;
        $this->ImageUrl = $_image_url;
    }

    public function GetXml() {
        $xml = '<button name="' . $this->Name . '"';

        if ($this->IsSubmited()) {
            $xml .= ' is-submited="true"';
        }

        if ($this->ImageUrl) {
            $xml .= ' image-url="' . $this->ImageUrl . '"';
        }

        $xml .= '><![CDATA[' . $this->Label . ']]></button>';
        return $xml;
    }

    public function IsSubmited() {
        global $_POST;

        return (isset($_POST[$this->Name]) || (isset($_POST[$this->Name . '_x']) && isset($_POST[$this->Name . '_y'])));
    }
}
