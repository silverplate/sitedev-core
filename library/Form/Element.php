<?php

define('FIELD_NO_UPDATE', 'no_update');
define('FIELD_SUCCESS', 'success');
define('FIELD_ERROR', 'error');
define('FIELD_ERROR_SPELLING', 'error_spelling');
define('FIELD_ERROR_REQUIRED', 'error_required');
define('FIELD_ERROR_EXIST', 'error_exist');

abstract class Core_Form_Element
{
    protected $Name;
    protected $Type;
    protected $Label;
    protected $Description;
    protected $Value;
    protected $IsRequired;
    protected $_isReadOnly;
    protected $Options = array();
    protected $OptionGroups = array();

    protected $AdditionalXml;
    protected $DbValueType;
    protected $UpdateType;
    protected $ErrorValue;
    protected $_errorMessage;

    public function __construct($_name, $_type, $_label = null, $_is_required = false) {
        $this->Name = $_name;
        $this->Type = $_type;
        $this->Label = $_label;
        $this->SetRequired($_is_required);
        $this->UpdateType = FIELD_NO_UPDATE;
    }

    public function SetName($_value) {
        $this->Name = $_value;
    }

    public function GetName() {
        return $this->Name;
    }

    public function GetType() {
        return $this->Type;
    }

    public function SetRequired($_is_required) {
        $this->IsRequired = ($_is_required);
    }

    public function GetValue() {
        return $this->Value;
    }

    public function setValue()
    {
        if (func_num_args() == 1) {
            $this->Value = Ext_Xml::decodeCdata(func_get_arg(0));

        } else if (func_num_args() == 2) {
            if (!is_array($this->Value)) $this->Value = array();
            $this->Value[func_get_arg(0)] = Ext_Xml::decodeCdata(func_get_arg(1));

        } else {
            $this->Value = '';
        }
    }

    public function setErrorValue()
    {
        if (func_num_args() == 1) {
            $value = func_get_arg(0);
            $this->ErrorValue = $value == 'NULL' ? '' : Ext_Xml::decodeCdata($value);

        } else if (func_num_args() == 2) {
            if (!is_array($this->ErrorValue)) $this->ErrorValue = array();

            $value = func_get_arg(1);
            $this->ErrorValue[func_get_arg(0)] = $value == 'NULL' ? '' : Ext_Xml::decodeCdata($value);
        }
    }

    public function AddOptionGroup($_label) {
        $this->OptionGroups[] = $_label;
        return count($this->OptionGroups) - 1;
    }

    public function AddOption($_value, $_label, $_group = null) {
        if (is_null($_group)) {
            $this->Options[] = array('value' => $_value, 'label' => $_label);

        } else {
            if (!isset($this->Options[$_group])) {
                $this->Options[$_group] = array();
            }

            $this->Options[$_group][] = array('value' => $_value, 'label' => $_label);
        }
    }

    public function RemoveOption($_value) {
        $options = array();

        for ($i = 0; $i < count($this->Options); $i++) {
            if ($this->Options[$i]['value'] != $_value) {
                $options[] = $this->Options[$i];
            }
        }

        $this->Options = $options;
    }

    /**
     * @param boolean|null $_isReadOnly
     * @return boolean
     */
    public function isReadOnly($_isReadOnly = null)
    {
        if ($_isReadOnly === true || $_isReadOnly === false) {
            $this->_isReadOnly = $_isReadOnly;
        }

        return (boolean) $this->_isReadOnly;
    }


    public function GetXml() {
        $xml = '<element name="' . $this->Name . '" type="' . $this->Type . '" update_type="'. $this->UpdateType . '"';

        if ($this->IsRequired) {
            $xml .= ' is-required="true"';
        }

        if ($this->isReadOnly()) {
            $xml .= ' is-readonly="true"';
        }

        $xml .= '>';

        if ($this->Label) {
            $xml .= Ext_Xml::cdata('label', $this->Label);
        }

        if ($this->Value != '') {
            $xml .= '<value>';

            if (is_array($this->Value)) {
                foreach ($this->Value as $key => $value) {
                    if ($value != 'NULL') {
                        $xml .= preg_match('/^[a-z_]+$/', $key)
                            ? Ext_Xml::cdata($key, $value)
                            : Ext_Xml::cdata('item', $value, array('key' => $key));
                    }
                }

            } else {
                $xml .= '<![CDATA[' . Ext_Xml::encodeCdata($this->Value) . ']]>';
            }

            $xml .= '</value>';
        }

        Ext_Xml::append(
            $xml,
            Ext_Xml::notEmptyCdata('description', $this->getDescription())
        );

        if ($this->Options) {
            $xml .= '<options>';

            if ($this->OptionGroups && isset($this->Options[0]) && is_array($this->Options[0])) {
                foreach ($this->OptionGroups as $group_id => $group_title) {
                    if (isset($this->Options[$group_id]) && $this->Options[$group_id]) {
                        $xml .= '<group><title><![CDATA[' . $group_title . ']]></title>';
                        foreach ($this->Options[$group_id] as $option) {
                            $xml .= '<item value="' . $option['value'] . '"><![CDATA[' . $option['label'] . ']]></item>';
                        }
                        $xml .= '</group>';
                    }
                }
            } else {
                foreach ($this->Options as $option) {
                    $xml .= '<item value="' . $option['value'] . '"><![CDATA[' . $option['label'] . ']]></item>';
                }
            }

            $xml .= '</options>';
        }

        if ($this->ErrorValue) {
            $xml .= '<error><value>';
            if (is_array($this->ErrorValue)) {
                foreach ($this->ErrorValue as $key => $value) {
                    $element = is_numeric($key) ? 'item' : $key;
                    $xml .= "<{$element}><![CDATA[{$value}]]></{$element}>";
                }
            } else {
                $xml .= '<![CDATA[' . $this->ErrorValue . ']]>';
            }
            $xml .= '</value></error>';
        }

        if ($this->_errorMessage) {
            $xml .= '<error-message><![CDATA[' .
                    $this->_errorMessage .
                    ']]></error-message>';
        }

        if ($this->GetAdditionalXml()) {
            $xml .= '<additional>' . $this->GetAdditionalXml() . '</additional>';
        }

        return $xml . '</element>';
    }

    public function getDescription()
    {
        return $this->Description;
    }

    public function setDescription($_value)
    {
        $this->Description = $_value;
    }

    public function addDescription($_value)
    {
        $value = $this->getDescription();
        if ($value) $value .= ' ';
        $value .= $_value;

        $this->setDescription($value);
    }

    public function IsUpdateSuccess() {
        return in_array($this->UpdateType, array(FIELD_NO_UPDATE, FIELD_SUCCESS));
    }

    public function IsUpdateError() {
        return !$this->IsUpdateSuccess();
    }

    public function GetUpdateType($_data) {
        $value = $this->ComputeValue($_data);

        if ($value === false) {
            $this->UpdateType = $this->CheckValue();
        } else {
            $this->UpdateType = $this->CheckValue($value);

            if ($this->IsUpdateSuccess()) $this->SetValue($value);
            else $this->SetErrorValue($value);
        }

        return $this->UpdateType;
    }

    public function SetUpdateType($_type) {
        $this->UpdateType = $_type;
    }

    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    public function setErrorMessage($_message)
    {
        $this->_errorMessage = $_message;
    }

    public function GetAdditionalXml() {
        return $this->AdditionalXml;
    }

    public function SetAdditionalXml($_value) {
        $this->AdditionalXml = $_value;
    }

    public function AddAdditionalXml($_value) {
        $this->AdditionalXml .= $_value;
    }

    public function ComputeValue($_data) {
        if (isset($_data[$this->Name])) {
            return $_data[$this->Name];
        } else {
            return false;
        }
    }

    public function CheckValue($_value = null) {
        if ($this->IsRequired && (is_null($_value) || $_value == '')) {
            return FIELD_ERROR_REQUIRED;

        } elseif (is_null($_value)) {
            return FIELD_NO_UPDATE;

        } else {
            return FIELD_SUCCESS;
        }
    }

    public function GetSqlValue() {
        if ($this->UpdateType == FIELD_SUCCESS) {
            return array ($this->Name => $this->GetValue());
        } else {
            return false;
        }
    }
}
