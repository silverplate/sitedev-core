<?php

abstract class Core_Form_Group
{
    private $Name;
    private $Title;
    private $Elements = array();
    private $IsSelected;
    private $AdditionalXml;

    public function __construct($_name, $_title, $_is_selected = false) {
        $this->Name = $_name;
        $this->Title = $_title;
        $this->SetSelected($_is_selected);
    }

    public function SetSelected($_is_selected) {
        $this->IsSelected = ($_is_selected);
    }

    public function GetElements() {
        return $this->Elements;
    }

    public function SetElements(&$_elements) {
        $this->Elements = $_elements;
    }

    public function AddElement(&$_element) {
        $this->Elements[] = $_element;
    }

    public function DeleteElement($_name) {
        foreach ($this->Elements as $key => $element) {
            if ($_name == $element->GetName()) {
                unset($this->Elements[$key]);
                $this->Elements = array_values($this->Elements);
                break;
            }
        }
    }

    public function SetAdditionalXml($_value) {
        $this->AdditionalXml = $_value;
    }

    public function AddAdditionalXml($_value) {
        $this->AdditionalXml .= $_value;
    }

    public function GetXml() {
        $result = '<group';
        if ($this->Name) $result .= ' name="' . $this->Name . '"';
        if ($this->IsSelected) $result .= ' is-selected="true"';
        $result .= '>';
        if ($this->Title) $result .= '<title><![CDATA[' . $this->Title . ']]></title>';
        if ($this->AdditionalXml) $result .= '<additional>' . $this->AdditionalXml . '</additional>';
        foreach ($this->Elements as $i) $result .= $i->GetXml();
        $result .= '</group>';
        return $result;
    }
}
