<?php

abstract class Core_Form_Element_CalendarDatetime extends App_Form_Element
{
    public function __construct($_name, $_type, $_label = null, $_is_required = false) {
        parent::__construct($_name, $_type, $_label, $_is_required);

        $hours_xml  = '<hours><item value="00">0</item>';
        for ($i = 6; $i < 24; $i++) {
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
        $result = array('date' => null,
                        'hours' => null,
                        'minutes' => null);
        if (
            !empty($_data[$this->Name]) &&
            $_data[$this->Name] != '0000-00-00 00:00:00'
        ) {
            $date = is_numeric($_data[$this->Name])
                  ? $_data[$this->Name]
                  : strtotime($_data[$this->Name]);

            $result = array(
                'date' => date('Y-m-d', $date),
                'hours' => date('H', $date),
                'minutes' => date('i', $date)
            );

            foreach (array('hours', 'minutes') as $i) {
                $name = $this->Name . '_' . $i;
                if (isset($_data[$name]) && (int) $_data[$name]) {
                    $result[$i] = sprintf('%02d', $_data[$name]);
                }
            }
        }

        return $result;
    }

    public function CheckValue($_value = null) {
        if ($_value) {
            $is_value = true;

//             foreach (array('date', 'hours', 'minutes') as $i) {
            foreach (array('date') as $i) {
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

//         } else if (!$is_value) {
//             return FIELD_NO_UPDATE;

        } else {
            return FIELD_SUCCESS;
        }
    }

    public function GetSqlValue() {
        if ($this->UpdateType == FIELD_SUCCESS) {
            $value = $this->GetValue();
            return array($this->Name => empty($value['date'])
                                      ? 'NULL'
                                      : "{$value['date']} {$value['hours']}:{$value['minutes']}:00");
        }

        return false;
    }
}
