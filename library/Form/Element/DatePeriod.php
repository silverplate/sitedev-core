<?php

abstract class Core_Form_Element_DatePeriod extends App_Form_Element
{
    public function ComputeValue($_data) {
        $value = array('from' => null, 'till' => null);

        if (
            !empty($_data[$this->Name . '_from']) &&
            $_data[$this->Name . '_from'] != '0000-00-00'
        ) {
            $value['from'] = $_data[$this->Name . '_from'];
        }

        if (
            !empty($_data[$this->Name . '_till']) &&
            $_data[$this->Name . '_till'] != '0000-00-00'
        ) {
            $value['till'] = $_data[$this->Name . '_till'];
        }

        return count($value) > 0 ? $value : false;
    }

    public function CheckValue($_value = null) {
        if (
            $this->IsRequired &&
            (empty($_value['from']) || empty($_value['till']))
        ) {
            return FIELD_ERROR_REQUIRED;

//         } else if (
//             !key_exists('from', $_value) &&
//             !key_exists('till', $_value)
//         ) {
//             return FIELD_NO_UPDATE;

        } else {
            return FIELD_SUCCESS;
        }
    }

    public function GetSqlValue() {
        if ($this->UpdateType == FIELD_SUCCESS) {
            $value = $this->GetValue();

            return array(
                $this->Name . '_from' => empty($value['from']) ? 'NULL' : $value['from'],
                $this->Name . '_till' => empty($value['till']) ? 'NULL' : $value['till']
            );
        }

        return false;
    }
}
