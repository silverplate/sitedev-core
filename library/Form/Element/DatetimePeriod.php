<?php

abstract class Core_Form_Element_DatetimePeriod extends App_Form_Element
{
    public function __construct($_name, $_type, $_label = null, $_is_required = false) {
        parent::__construct($_name, $_type, $_label, $_is_required);

        $hours_xml = '<hours>';
        for ($i = 0; $i < 24; $i++) {
            $value = sprintf('%02d', $i);
            $hours_xml .= '<item value="' . $value . '">' . $i . '</item>';
        }
        $hours_xml .= '</hours>';

        $minutes_xml = '<minutes>';
        for ($i = 0; $i < 60; $i = $i + 10) {
            $value = sprintf('%02d', $i);
            $minutes_xml .= '<item value="' . $value . '">' . $i . '</item>';
        }
        $minutes_xml .= '</minutes>';

        $this->AddAdditionalXml($hours_xml . $minutes_xml);
    }

    public function ComputeValue($_data) {
        $result = array('from' => null,
                        'from_hours' => null,
                        'from_minutes' => null,
                        'till' => null,
                        'till_hours' => null,
                        'till_minutes' => null);
        if (
            !empty($_data[$this->Name . '_from']) &&
            $_data[$this->Name . '_from'] != '0000-00-00'
        ) {
            $from = is_numeric($_data[$this->Name . '_from'])
                  ? $_data[$this->Name . '_from']
                  : strtotime($_data[$this->Name . '_from']);

            $result['from'] = date('Y-m-d', $from);
            $result['from_hours'] = date('H', $from);
            $result['from_minutes'] = date('i', $from);
        }

        if (
            !empty($_data[$this->Name . '_till']) &&
            $_data[$this->Name . '_till'] != '0000-00-00'
        ) {
            $till = is_numeric($_data[$this->Name . '_till'])
                  ? $_data[$this->Name . '_till']
                  : strtotime($_data[$this->Name . '_till']);

            $result['till'] = date('Y-m-d', $till);
            $result['till_hours'] = date('H', $till);
            $result['till_minutes'] = date('i', $till);
        }

        foreach (
            array('from_hours', 'from_minutes', 'till_hours', 'till_minutes')
            as $i
        ) {
            $name = $this->Name . '_' . $i;

            if (isset($_data[$name]) && (int) $_data[$name]) {
                $result[$i] = sprintf('%02d', $_data[$name]);
            }
        }

        return $result;
    }

    public function CheckValue($_value = null) {
        if ($_value) {
            $is_value = true;
//             foreach (array('from', 'from_hours', 'from_minutes', 'till', 'till_hours', 'till_minutes') as $i) {
            foreach (array('from', 'till') as $i) {
                if (empty($_value[$i])) {
                    $is_value = false;
                    break;
                }
            }

        } else {
            $is_value = false;
        }

        if ($this->IsRequired && !$is_value) {
            return FIELD_ERROR_REQUIRED;

//         } elseif (!$is_value) {
//             return FIELD_NO_UPDATE;

        } else {
            return FIELD_SUCCESS;
        }
    }

    public function GetSqlValue() {
        if ($this->UpdateType == FIELD_SUCCESS) {
            $value = $this->GetValue();

            return array(
                $this->Name . '_from' => empty($value['from']) ? 'NULL' : "{$value['from']} {$value['from_hours']}:{$value['from_minutes']}:00",
                $this->Name . '_till' => empty($value['till']) ? 'NULL' : "{$value['till']} {$value['till_hours']}:{$value['till_minutes']}:00"
            );
        }

        return false;
    }
}
