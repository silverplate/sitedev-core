<?php

abstract class Core_Form_Element_Email extends App_Form_Element
{
    public function CheckValue($_value = null) {
        if ($this->IsRequired && (is_null($_value) || $_value == '')) {
            return FIELD_ERROR_REQUIRED;

        } elseif (is_null($_value)) {
            return FIELD_NO_UPDATE;

        } elseif ($_value != '' && !preg_match('/^[0-9a-zA-Z_][0-9a-zA-Z_.-]*[0-9a-zA-Z_-]@([0-9a-zA-Z][0-9a-zA-Z-]*\.)+[a-zA-Z]{2,4}$/', $_value)) {
            return FIELD_ERROR_SPELLING;

        } else {
            return FIELD_SUCCESS;
        }
    }
}
