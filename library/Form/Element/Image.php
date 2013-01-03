<?php

abstract class Core_Form_Element_Image extends App_Form_Element
{
    public function SetImage($_path) {
        $this->SetValue($this->ComputeValue($_path));
    }

    public function ComputeValue($_data) {
        $value = array();

        if (is_array($_data) && isset($_data[$this->Name]) && $_data[$this->Name] && is_array($_data[$this->Name]) && $_data[$this->Name]['name']) {
            $value = array(
                'name' => $_data[$this->Name]['name'],
                'tmp_name' => $_data[$this->Name]['tmp_name']
            );

        } elseif ((is_array($_data) && isset($_data[$this->Name]) && is_string($_data[$this->Name]) && $_data[$this->Name] && is_file($_data[$this->Name])) || (is_string($_data) && $_data && is_file($_data))) {
            $filepath = is_array($_data) ? $_data[$this->Name] : $_data;
            $size = getimagesize($filepath);
            if ($size) {
                $value = array(
                    'path' => $filepath,
                    'url' => str_replace(DOCUMENT_ROOT, '/', $filepath),
                    'width' => $size[0],
                    'height' => $size[1],
                    'type' => $size[2],
                    'size' => Ext_Number::format(filesize($filepath) / 1024)
                );
            }
        }

        return $value;
    }

    public function CheckValue($_value = null) {
        if ($this->IsRequired && (!$_value || !isset($_value['name']) || !isset($_value['tmp_name']) || !$_value['name'] || !$_value['tmp_name'])) {
            return FIELD_ERROR_REQUIRED;

        } else if (!($_value && isset($_value['name']) && isset($_value['tmp_name']) && $_value['name'] && $_value['tmp_name'])) {
            return FIELD_NO_UPDATE;

        } else if (
            !Ext_Image::isImageExt(Ext_File::computeExt($_value['name'])) ||
            !getimagesize($_value['tmp_name'])
        ) {
            return FIELD_ERROR_SPELLING;

        } else {
            return FIELD_SUCCESS;
        }
    }

    public function GetSqlValue() {
        if ($this->UpdateType == FIELD_SUCCESS) {
            $value = $this->GetValue();
            if ($value) {
                if (is_array($value) && isset($value['name']) && $value['name']) {
                    return array($this->Name => $value['name']);

                } elseif (is_file($value)) {
                    return array($this->Name => basename($value));
                }
            }
        }

        return false;
    }
}
