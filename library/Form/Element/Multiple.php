<?php

abstract class Core_Form_Element_Multiple extends App_Form_Element
{
    public function ComputeValue($_data) {
        if (isset($_data[$this->Name])) {
            if (is_array($_data[$this->Name])) {
                $value = array();

                foreach ($_data[$this->Name] as $item) {
                    $value[] = $item;
                }

                return $value;

            } else {
                return array($_data[$this->Name]);
            }

        } else {
            return array();
        }
    }

    public function CheckValue($_value = null) {
        return $this->IsRequired && (!$_value || !is_array($_value))
            ? FIELD_ERROR_REQUIRED
            : FIELD_SUCCESS;
    }

    public function GetSqlValue() {
        return $this->UpdateType == FIELD_SUCCESS
            ? array($this->Name => implode(', ', $this->GetValue()))
            : false;
    }
}
