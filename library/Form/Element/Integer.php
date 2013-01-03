<?php

abstract class Core_Form_Element_Integer extends App_Form_Element
{
    public function checkValue($_value = null)
    {
        if ($this->IsRequired && (is_null($_value) || $_value == '')) {
            return FIELD_ERROR_REQUIRED;

        } else if (is_null($_value)) {
            return FIELD_NO_UPDATE;

        } else if (
            $_value != '' &&
            $_value != '0' &&
            !Ext_Number::isInteger($_value)
        ) {
            return FIELD_ERROR_SPELLING;

        } else {
            return FIELD_SUCCESS;
        }
    }
}
