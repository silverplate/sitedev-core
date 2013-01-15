<?php

require_once realpath(dirname(__FILE__) . '/../../library') . '/libs.php';
require_once SETS . 'project.php';

$nl = PHP_EOL;

$res = Ext_Db::get()->execute('
    ALTER TABLE `' . DB_PREFIX . 'back_section`
    MODIFY `description` TEXT NULL
');

echo $res ? 'Готово' : 'Ошибка';
echo $nl . $nl;
