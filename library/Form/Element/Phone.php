<?php

abstract class Core_Form_Element_Phone extends App_Form_Element
{
    public function ComputeValue($_data) {
        $value = array();

        if (isset($_data[$this->Name])) {
            $value['number'] = $_data[$this->Name];

            if (isset($_data[$this->Name . '_code'])) {
                $value['code'] = $_data[$this->Name . '_code'];
            }

            return $value;

        } elseif (isset($_data[$this->Name . '_number'])) {
            $value['number'] = $_data[$this->Name . '_number'];

            if (isset($_data[$this->Name . '_code'])) {
                $value['code'] = $_data[$this->Name . '_code'];
            }

            return $value;

        } else {
            return false;
        }
    }

    public function CheckValue($_value = null) {
        if ($this->IsRequired && !(isset($_value['number']) && $_value['number'])) {
            return FIELD_ERROR_REQUIRED;

        } elseif (!isset($_value['number'])) {
            return FIELD_NO_UPDATE;

        } else {
            return FIELD_SUCCESS;
        }
    }

    public function GetSqlValue() {
        if ($this->UpdateType == FIELD_SUCCESS) {
            $result = array();
            $value = $this->GetValue();

            if (isset($value['number'])) {
                $result[$this->Name] = $value['number'];
            }

            if (isset($value['code'])) {
                $result[$this->Name . '_code'] = $value['code'];
            }

            return $result;
        }

        return false;
    }
}
