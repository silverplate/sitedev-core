<?php

namespace Core\Cms\Ext;

abstract class File extends \Ext\File
{
    /**
     * @param string $_path
     * @param string $_pathStartsWith
     * @param string $_uriStartsWith
     * @return \App\Cms\Ext\File|\App\Cms\Ext\Image
     */
    public static function factory($_path, $_pathStartsWith = null, $_uriStartsWith = null)
    {
        $class = self::isImageExt(self::computeExt($_path))
               ? '\App\Cms\Ext\Image'
               : '\App\Cms\Ext\File';

        return new $class($_path, $_pathStartsWith, $_uriStartsWith);
    }

    /**
     * Usage example:
     * if (!\App\Cms\Ext\File::checkUploadSize()) {
     *     $form->UpdateStatus = FORM_ERROR;
     *     $oversized = \App\Cms\Ext\File::getOversizedFields();
     *
     *     if (count($oversized) > 0) {
     *         foreach ($oversized as $field) {
     *             $form->Elements[$field]->setUpdateType(FIELD_ERROR);
     *             $form->Elements[$field]->setErrorMessage(
     *                 \App\Cms\Ext\File::getOversizedErrorMessage()
     *             );
     *         }
     *
     *     } else {
     *         $form->Elements['files']->setUpdateType(FIELD_ERROR);
     *         $form->Elements['files']->setErrorMessage(
     *             \App\Cms\Ext\File::getOversizedErrorMessage()
     *         );
     *     }
     * }
     */
    public static function checkUploadSize($_filter = null)
    {
        global $gMaxUploadFilesize, $gAmountMaxUploadFilesize;

        if (empty($gMaxUploadFilesize) && empty($gAmountMaxUploadFilesize)) {
            return true;
        }

        if (
            !empty($gAmountMaxUploadFilesize) &&
            self::getUploadAmountSize($_filter) > $gAmountMaxUploadFilesize
        ) {
            return false;
        }

        if (
            !empty($gMaxUploadFilesize) &&
            count(self::getOversizedFields($_filter)) > 0
        ) {
            return false;
        }

        return true;
    }

    /**
     * For usage example see \App\Cms\Ext\File::checkUploadSize().
     */
    public static function getUploadAmountSize($_filter = null)
    {
        if ($_filter) {
            $filter = is_array($_filter) ? $_filter : array($_filter);
            $filter = array_intersect($filter, array_keys($_FILES));
        } else {
            $filter = array_keys($_FILES);
        }

        $amount = 0;

        foreach ($filter as $field) {
            $data = $_FILES[$field];

            if (is_array($data['name'])) {
                for ($i = 0; $i < count($data['name']); $i++) {
                    $amount += $data['size'][$i] / 1024 / 1024;
                }

            } else {
                $amount += $data['size'] / 1024 / 1024;
            }
        }

        return $amount;
    }

    /**
     * For usage example see \App\Cms\Ext\File::checkUploadSize().
     */
    public static function getOversizedFields($_filter = null)
    {
        global $gMaxUploadFilesize;

        if (empty($gMaxUploadFilesize)) {
            return true;
        }

        if ($_filter) {
            $filter = is_array($_filter) ? $_filter : array($_filter);
            $filter = array_intersect($filter, array_keys($_FILES));
        } else {
            $filter = array_keys($_FILES);
        }

        $fields = array();

        foreach ($filter as $field) {
            $data = $_FILES[$field];
            $isError = false;

            if (is_array($data['name'])) {
                for ($i = 0; $i < count($data['name']); $i++) {
                    $mbSize = $data['size'][$i] / 1024 / 1024;

                    if ($mbSize > $gMaxUploadFilesize) {
                        $isError = true;
                        break;
                    }
                }

            } else {
                $mbSize = $data['size'] / 1024 / 1024;
                $isError = $mbSize > $gMaxUploadFilesize;
            }

            if ($isError) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * For usage example see \App\Cms\Ext\File::checkUploadSize().
     */
    public static function getOversizedErrorMessage()
    {
        global $gMaxUploadFilesize, $gAmountMaxUploadFilesize;

        return 'Превышен максимальный размер для&nbsp;загрузки. Лимиты: ' .
               \Ext\Number::format($gMaxUploadFilesize) .
               '&nbsp;МБ для&nbsp;одного файла и&nbsp;' .
               \Ext\Number::format($gAmountMaxUploadFilesize) .
               '&nbsp;МБ всего.';
    }

    /**
     * Usage example:
     * $form->Elements['files']->addDescription(
     *     \App\Cms\Ext\File::getOversizedWarningMessage()
     * );
     */
    public static function getOversizedWarningMessage()
    {
        global $gMaxUploadFilesize, $gAmountMaxUploadFilesize;

        if (empty($gMaxUploadFilesize) && empty($gAmountMaxUploadFilesize)) {
            return '';
        }

        return 'Общий размер всех загружаемых за&nbsp;раз файлов ' .
               'не&nbsp;должен превышать ' .
               \Ext\Number::format($gAmountMaxUploadFilesize) .
               '&nbsp;МБ и&nbsp;каждый файл должен быть меньше ' .
               \Ext\Number::format($gMaxUploadFilesize) .
               '&nbsp;МБ.';
    }

    public static function concatUrl($_uri = null, $_host = null)
    {
        global $gHost;
        return parent::concatUrl($_uri, is_null($_host) ? $gHost : $_host);
    }
}
