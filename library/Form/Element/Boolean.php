<?php

abstract class Core_Form_Element_Boolean extends App_Form_Element
{
    public function ComputeValue($_data) {
        return (isset($_data[$this->Name]) && $_data[$this->Name]) ? 1 : 0;
    }

    public function CheckValue($_value = null) {
        if ($this->IsRequired && (is_null($_value) || $_value != '1')) {
            return FIELD_ERROR_REQUIRED;

        } elseif (is_null($_value)) {
            return FIELD_NO_UPDATE;

        } else {
            return FIELD_SUCCESS;
        }
    }

    public function GetSqlValue() {
        if ($this->UpdateType == FIELD_SUCCESS) {
            return array($this->Name => ($this->GetValue()) ? '1' : '0');
        }
        return false;
    }
}
