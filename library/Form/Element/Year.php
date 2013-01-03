<?php

abstract class Core_Form_Element_Year extends App_Form_Element
{
    public function CheckValue($_value = null) {
        if ($this->IsRequired && (is_null($_value) || $_value == '')) {
            return FIELD_ERROR_REQUIRED;

        } elseif (is_null($_value)) {
            return FIELD_NO_UPDATE;

        } elseif ($_value != '' && ((int) $_value < 1901 || (int) $_value > 2155)) {
            return FIELD_ERROR_SPELLING;

        } else {
            return FIELD_SUCCESS;
        }
    }
}
