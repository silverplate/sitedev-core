<?php

abstract class Core_Form_Element_Password extends App_Form_Element
{
    public function ComputeValue($_data) {
        $value = array();

        if (isset($_data[$this->Name]) && $_data[$this->Name] != '') {
            $value['password'] = $_data[$this->Name];

            if (isset($_data[$this->Name . '_check'])) {
                $value['check'] = $_data[$this->Name . '_check'];
            }

            return $value;

        } else {
            return false;
        }
    }

    public function CheckValue($_value = null) {
        if ($this->IsRequired && (!(isset($_value['password']) && $_value['password']) || !(isset($_value['check']) && $_value['check']))) {
            return FIELD_ERROR_REQUIRED;

        } elseif (!isset($_value['password']) && !isset($_value['check'])) {
            return FIELD_NO_UPDATE;

        } elseif (isset($_value['password']) && isset($_value['check']) && $_value['password'] == $_value['check']) {
            return FIELD_SUCCESS;

        } else {
            return FIELD_ERROR_SPELLING;
        }
    }

    public function GetSqlValue() {
        if ($this->UpdateType == FIELD_SUCCESS) {
            $value = $this->GetValue();
            if ($value) {
                return array($this->Name => $value['password']);
            }
        }

        return false;
    }
}
