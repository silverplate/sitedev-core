<?php

require_once realpath(dirname(__FILE__) . '/../../library') . '/libs.php';
initSettings();

$nl = PHP_EOL;

$res = Ext_Db::get()->execute('
    ALTER TABLE `' . Ext_Db::get()->getPrefix() . 'back_section`
    MODIFY `description` TEXT NULL
');

echo $res ? 'Готово' : 'Ошибка';
echo $nl . $nl;
