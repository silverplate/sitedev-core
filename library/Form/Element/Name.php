<?php

abstract class Core_Form_Element_Name extends App_Form_Element
{
    public function ComputeValue($_data) {
        $value = array();

        if (isset($_data['first_name']) || isset($_data['last_name'])) {
            if (isset($_data['first_name'])) {
                $value['first_name'] = $_data['first_name'];
            }

            if (isset($_data['last_name'])) {
                $value['last_name'] = $_data['last_name'];
            }

            if (isset($_data['patronymic_name'])) {
                $value['patronymic_name'] = $_data['patronymic_name'];
            }

            if (isset($_data['middle_name'])) {
                $value['middle_name'] = $_data['middle_name'];
            }

            return $value;

        } else if (
            isset($_data[$this->Name . '_first_name']) ||
            isset($_data[$this->Name . '_last_name'])
        ) {
            if (isset($_data[$this->Name . '_first_name'])) {
                $value['first_name'] = $_data[$this->Name . '_first_name'];
            }

            if (isset($_data[$this->Name . '_last_name'])) {
                $value['last_name'] = $_data[$this->Name . '_last_name'];
            }

            if (isset($_data[$this->Name . '_patronymic_name'])) {
                $value['patronymic_name'] = $_data[$this->Name . '_patronymic_name'];
            }

            if (isset($_data[$this->Name . '_middle_name'])) {
                $value['middle_name'] = $_data[$this->Name . '_middle_name'];
            }

            return $value;

        } else {
            return false;
        }
    }

    public function CheckValue($_value = null) {
        if (
            $this->IsRequired &&
            (empty($_value['first_name']) || empty($_value['last_name']))
        ) {
            return FIELD_ERROR_REQUIRED;

        } elseif (!isset($_value['first_name']) && !isset($_value['last_name'])) {
            return FIELD_NO_UPDATE;

        } else {
            return FIELD_SUCCESS;
        }
    }

    public function GetSqlValue() {
        if ($this->UpdateType == FIELD_SUCCESS) {
            $result = array();
            $value = $this->GetValue();

            if (isset($value['first_name'])) {
                $result['first_name'] = $value['first_name'];
            }

            if (isset($value['last_name'])) {
                $result['last_name'] = $value['last_name'];
            }

            if (isset($value['patronymic_name'])) {
                $result['patronymic_name'] = $value['patronymic_name'];
            }

            if (isset($value['middle_name'])) {
                $result['middle_name'] = $value['middle_name'];
            }

            return $result;
        }

        return false;
    }
}
