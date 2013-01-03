<?php

abstract class Core_Form_Element_Uri extends App_Form_Element
{
    public function CheckValue($_value = null) {
        if ($this->IsRequired && (is_null($_value) || $_value == '')) {
            return FIELD_ERROR_REQUIRED;

        } elseif (is_null($_value)) {
            return FIELD_NO_UPDATE;

        } elseif ($_value != '' && (!preg_match('/^[a-zA-Z_0-9\-\/]+$/', $_value) || strpos($_value, '//') !== false)) {
            return FIELD_ERROR_SPELLING;

        } else {
            return FIELD_SUCCESS;
        }
    }
}
