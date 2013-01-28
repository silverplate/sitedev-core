<?php

require_once realpath(dirname(__FILE__) . '/../../library') . '/libs.php';
initSettings();

$nl = PHP_EOL;
$db = Ext_Db::get();


/**
 * Таблица front_document
 */

$key = App_Cms_Front_Document::getPri();
$tbl = App_Cms_Front_Document::getTbl();
$link1Tbl = App_Cms_Front_Document_Has_Navigation::getTbl();
$link2Tbl = App_Cms_Front_Data::getTbl();


// Установка числовых ключей

$db->execute("ALTER TABLE `$tbl` DROP FOREIGN KEY `fk_{$tbl}_parent_id`");
$i = 0;

foreach ($db->getList("SELECT `$key` FROM `$tbl` ORDER BY `sort_order`") as $id) {
    $i++;
    $db->execute("UPDATE `$tbl` SET `$key` = $i WHERE `$key` = '$id'");
    $db->execute("UPDATE `$tbl` SET `parent_id` = $i WHERE `parent_id` = '$id'");
}


// Удаление внешних ключей

$db->execute("ALTER TABLE `$link1Tbl` DROP FOREIGN KEY `fk_{$link1Tbl}_{$key}`");
$db->execute("ALTER TABLE `$link2Tbl` DROP FOREIGN KEY `fk_{$link2Tbl}_{$key}`");


// Изменение типа поля

$db->execute("
    ALTER TABLE `$tbl`
    MODIFY `$key` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MODIFY `sort_order` SMALLINT UNSIGNED NULL,
    MODIFY `parent_id` SMALLINT UNSIGNED NULL
");

$db->execute("ALTER TABLE `$link1Tbl` MODIFY `$key` SMALLINT UNSIGNED NOT NULL");
$db->execute("ALTER TABLE `$link2Tbl` MODIFY `$key` SMALLINT UNSIGNED NOT NULL");


// Добавление ключей для связанных таблиц

$db->execute("
    ALTER TABLE `$tbl`
    ADD CONSTRAINT `fk_{$tbl}_parent_id`
    FOREIGN KEY (`parent_id`)
    REFERENCES `$tbl` (`$key`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
");

$db->execute("
    ALTER TABLE `$link1Tbl`
    ADD CONSTRAINT `fk_{$link1Tbl}_{$key}`
    FOREIGN KEY (`$key`)
    REFERENCES `$tbl` (`$key`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
");

$db->execute("
    ALTER TABLE `$link2Tbl`
    ADD CONSTRAINT `fk_{$link2Tbl}_{$key}`
    FOREIGN KEY (`$key`)
    REFERENCES `$tbl` (`$key`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
");


/**
 * Таблица front_data
 */

$key = App_Cms_Front_Data::getPri();
$tbl = App_Cms_Front_Data::getTbl();


// Установка числовых ключей

$i = 0;

foreach ($db->getList("SELECT `$key` FROM `$tbl` ORDER BY `sort_order`") as $id) {
    $i++;
    $db->execute("UPDATE `$tbl` SET `$key` = $i WHERE `$key` = '$id'");
}


// Изменение типа поля

$db->execute("
    ALTER TABLE `$tbl`
    MODIFY `$key` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MODIFY `sort_order` SMALLINT UNSIGNED NULL
");


/**
 * Таблица front_controller
 */

$key = App_Cms_Front_Controller::getPri();
$tbl = App_Cms_Front_Controller::getTbl();
$link1Tbl = App_Cms_Front_Document::getTbl();
$link2Tbl = App_Cms_Front_Data::getTbl();


// Установка числовых ключей

$i = 0;

foreach ($db->getList("SELECT `$key` FROM `$tbl`") as $id) {
    $i++;
    $db->execute("UPDATE `$tbl` SET `$key` = $i WHERE `$key` = '$id'");
}


// Удаление внешних ключей

$db->execute("ALTER TABLE `$link1Tbl` DROP FOREIGN KEY `fk_{$link1Tbl}_{$key}`");
$db->execute("ALTER TABLE `$link2Tbl` DROP FOREIGN KEY `fk_{$link2Tbl}_{$key}`");


// Изменение типа поля

$db->execute("
    ALTER TABLE `$tbl`
    MODIFY `$key` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT
");

$db->execute("ALTER TABLE `$link1Tbl` MODIFY `$key` SMALLINT UNSIGNED NULL");
$db->execute("ALTER TABLE `$link2Tbl` MODIFY `$key` SMALLINT UNSIGNED NULL");


// Добавление ключей для связанных таблиц

$db->execute("
    ALTER TABLE `$link1Tbl`
    ADD CONSTRAINT `fk_{$link1Tbl}_{$key}`
    FOREIGN KEY (`$key`)
    REFERENCES `$tbl` (`$key`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
");

$db->execute("
    ALTER TABLE `$link2Tbl`
    ADD CONSTRAINT `fk_{$link2Tbl}_{$key}`
    FOREIGN KEY (`$key`)
    REFERENCES `$tbl` (`$key`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
");


/**
 * Таблица front_navigation
 */

$key = App_Cms_Front_Navigation::getPri();
$tbl = App_Cms_Front_Navigation::getTbl();
$link1Tbl = App_Cms_Front_Document_Has_Navigation::getTbl();


// Установка числовых ключей

$i = 0;

foreach ($db->getList("SELECT `$key` FROM `$tbl`") as $id) {
    $i++;
    $db->execute("UPDATE `$tbl` SET `$key` = $i WHERE `$key` = '$id'");
}


// Удаление внешних ключей

$db->execute("ALTER TABLE `$link1Tbl` DROP FOREIGN KEY `fk_{$link1Tbl}_{$key}`");


// Изменение типа поля

$db->execute("
    ALTER TABLE `$tbl`
    MODIFY `$key` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MODIFY `sort_order` SMALLINT UNSIGNED NULL
");

$db->execute("ALTER TABLE `$link1Tbl` MODIFY `$key` SMALLINT UNSIGNED NOT NULL");


// Добавление ключей для связанных таблиц

$db->execute("
    ALTER TABLE `$link1Tbl`
    ADD CONSTRAINT `fk_{$link1Tbl}_{$key}`
    FOREIGN KEY (`$key`)
    REFERENCES `$tbl` (`$key`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
");


/**
 * Таблица front_data_content_type
 */

$key = App_Cms_Front_Data_ContentType::getPri();
$tbl = App_Cms_Front_Data_ContentType::getTbl();


// Изменение типа поля

$db->execute("
    ALTER TABLE `$tbl`
    MODIFY `sort_order` SMALLINT UNSIGNED NOT NULL
");


/**
 * Таблица back_user
 */

$key = App_Cms_Back_User::getPri();
$tbl = App_Cms_Back_User::getTbl();
$link1Tbl = App_Cms_Back_User_Has_Section::getTbl();
$link2Tbl = App_Cms_Back_Log::getTbl();


// Установка числовых ключей

$i = 0;

foreach ($db->getList("SELECT `$key` FROM `$tbl`") as $id) {
    $i++;
    $db->execute("UPDATE `$tbl` SET `$key` = $i WHERE `$key` = '$id'");
}


// Удаление внешних ключей

$db->execute("ALTER TABLE `$link1Tbl` DROP FOREIGN KEY `fk_{$link1Tbl}_{$key}`");
$db->execute("ALTER TABLE `$link2Tbl` DROP FOREIGN KEY `fk_{$link2Tbl}_{$key}`");


// Изменение типа поля

$db->execute("
    ALTER TABLE `$tbl`
    MODIFY `$key` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT
");

$db->execute("ALTER TABLE `$link1Tbl` MODIFY `$key` SMALLINT UNSIGNED NOT NULL");
$db->execute("ALTER TABLE `$link2Tbl` MODIFY `$key` SMALLINT UNSIGNED NULL");


// Добавление ключей для связанных таблиц

$db->execute("
    ALTER TABLE `$link1Tbl`
    ADD CONSTRAINT `fk_{$link1Tbl}_{$key}`
    FOREIGN KEY (`$key`)
    REFERENCES `$tbl` (`$key`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
");

$db->execute("
    ALTER TABLE `$link2Tbl`
    ADD CONSTRAINT `fk_{$link2Tbl}_{$key}`
    FOREIGN KEY (`$key`)
    REFERENCES `$tbl` (`$key`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
");


/**
 * Таблица back_section
 */

$key = App_Cms_Back_Section::getPri();
$tbl = App_Cms_Back_Section::getTbl();
$link1Tbl = App_Cms_Back_User_Has_Section::getTbl();
$link2Tbl = App_Cms_Back_Log::getTbl();


// Установка числовых ключей

$i = 0;

foreach ($db->getList("SELECT `$key` FROM `$tbl`") as $id) {
    $i++;
    $db->execute("UPDATE `$tbl` SET `$key` = $i WHERE `$key` = '$id'");
}


// Удаление внешних ключей

$db->execute("ALTER TABLE `$link1Tbl` DROP FOREIGN KEY `fk_{$link1Tbl}_{$key}`");
$db->execute("ALTER TABLE `$link2Tbl` DROP FOREIGN KEY `fk_{$link2Tbl}_{$key}`");


// Изменение типа поля

$db->execute("
    ALTER TABLE `$tbl`
    MODIFY `$key` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MODIFY `sort_order` SMALLINT UNSIGNED NULL
");

$db->execute("ALTER TABLE `$link1Tbl` MODIFY `$key` SMALLINT UNSIGNED NOT NULL");
$db->execute("ALTER TABLE `$link2Tbl` MODIFY `$key` SMALLINT UNSIGNED NULL");


// Добавление ключей для связанных таблиц

$db->execute("
    ALTER TABLE `$link1Tbl`
    ADD CONSTRAINT `fk_{$link1Tbl}_{$key}`
    FOREIGN KEY (`$key`)
    REFERENCES `$tbl` (`$key`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
");

$db->execute("
    ALTER TABLE `$link2Tbl`
    ADD CONSTRAINT `fk_{$link2Tbl}_{$key}`
    FOREIGN KEY (`$key`)
    REFERENCES `$tbl` (`$key`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
");


echo $nl . $nl;
