<?php

abstract class Core_Form_Element_Date extends App_Form_Element
{
    public function ComputeValue($_data) {
        $value = array();

        if (!empty($_data[$this->Name]) && $_data[$this->Name] != '0000-00-00') {
            $date = strtotime($_data[$this->Name]);
            $value = array('day' => date('d', $date), 'month' => date('m', $date), 'year' => date('Y', $date));

            return $value;

        } elseif (isset($_data[$this->Name . '_day']) || isset($_data[$this->Name . '_month']) || isset($_data[$this->Name . '_year'])) {
            foreach (array('year', 'month', 'day') as $i) {
                if (isset($_data[$this->Name . '_' . $i])) {
                    $value[$i] = $_data[$this->Name . '_' . $i];
                }
            }

            return $value;

        } else {
            return false;
        }
    }

    public function CheckValue($_value = null) {
        $is_value = true;

        foreach (array('year', 'month', 'day') as $i) {
            if (!(isset($_value[$i]) && $_value[$i])) {
                $is_value = false;
                break;
            }
        }

        if ($this->IsRequired && !$is_value) {
            return FIELD_ERROR_REQUIRED;

        } elseif (!$is_value) {
            return FIELD_NO_UPDATE;

        } elseif (!checkdate((int) $_value['month'], (int) $_value['day'], (int) $_value['year'])) {
            return FIELD_ERROR_SPELLING;

        } else {
            return FIELD_SUCCESS;
        }
    }

    public function GetSqlValue() {
        if ($this->UpdateType == FIELD_SUCCESS) {
            $value = $this->GetValue();
            if ($value) {
                $result = array($this->Name => '');

                foreach (array('year', 'month', 'day') as $i) {
                    $result[$this->Name] .= ($result[$this->Name] ? '-' : '') . $value[$i];
                }

                return $result;
            }
        }
        return false;
    }
}
