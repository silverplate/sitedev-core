<?php

require_once realpath(dirname(__FILE__) . '/../../../core/library') . '/libs.php';
require_once CORE_SETS . 'project.php';
$result = array();

// Init for DB

$backSections = array(
    array('title' => 'Страницы', 'uri' => 'cms-pages', 'description' => 'Работа с навигацией и информационным наполнением страниц сайта.'),
    array('title' => 'Пользователи', 'uri' => 'users', 'description' => 'Редактирование пользователей сайта.', 'is_published' => 0),
    array('title' => 'Контроллеры', 'uri' => 'cms-controllers', 'description' => 'Управление контроллерами страниц сайта и блоков данных.'),
    array('title' => 'Шаблоны', 'uri' => 'cms-templates', 'description' => 'Управление шаблонами сайта.'),
    array('title' => 'Типы навигации', 'uri' => 'cms-navigation', 'description' => 'Редактирование типов навигации.', 'is_published' => 0),
    array('title' => 'Пользователи СУ', 'uri' => 'cms-users', 'description' => 'Редактирование пользователей СУ.'),
    array('title' => 'Разделы СУ', 'uri' => 'cms-sections', 'description' => 'Редактирование разделов СУ.'),
    array('title' => 'Логи СУ', 'uri' => 'cms-logs', 'description' => 'Просмотр действий пользователей системы управления.', 'is_published' => 0)
);

$backUsers = array(
    array('title' => 'Разработчик', 'login' => 'developer', 'passwd' => Ext_String::getRandomReadable(8), 'email' => 'support@sitedev.ru')
);

$frontControllers = array(
    'common' => array('title' => 'Страница сайта', 'type_id' => 1, 'filename' => 'Common.php', 'is_document_main' => 1, 'is_multiple' => 1),
    'not-found' => array('title' => 'Документ не найден', 'type_id' => 1, 'filename' => 'NotFound.php', 'is_document_main' => 0, 'is_multiple' => 0),
    'sitemap' => array('title' => 'Карта сайта для поисковых роботов', 'type_id' => 1, 'filename' => 'RobotsSitemap.php', 'is_document_main' => 0, 'is_multiple' => 0),
    'subpage-navigation' => array('title' => 'Вложенная навигация', 'type_id' => 2, 'filename' => 'SubpageNavigation.php', 'is_document_main' => 0, 'is_multiple' => 1)
);

$templates = array(
    'common' => array('title' => 'Основной', 'filename' => 'page.xsl'),
    'modules' => array('title' => 'Общее', 'filename' => 'site-common.xsl', 'is_document_main' => 0)
);

$frontDocuments = array(
    array('/' => array('title' => SITE_TITLE,
                       'folder' => '/',
                       'сontroller' => 'common',
                       'template' => 'common',
                       'navigations' => array('main'))),

    array('/not-found/' => array('title' => 'Документ не найден',
                                 'folder' => 'not-found',
                                 'сontroller' => 'not-found',
                                 'template' => 'common'))
);

$frontNavigations = array(
    'main' => array('title' => 'Основная', 'name' => 'main', 'type' => 'tree'),
    'service' => array('title' => 'Сервисная', 'name' => 'service', 'type' => 'list', 'is_published' => 0)
);

$frontDataContentType = array(
    'string' => array('title' => 'Строка'),
    'text' => array('title' => 'Текст'),
    'integer' => array('title' => 'Целое число'),
    'xml' => array('title' => 'XML')
);

$content1  = "<h2>Шарапова поможет детям Чернобыля</h2>\r\n";
$content1 .= "<p>Российская теннисистка Мария Шарапова, являющаяся послом доброй воли ООН, планирует посетить Чернобыль. Как сообщает AP, Шарапова посетит область, прилегающую к&nbsp;АЭС, летом следующего года после &laquo;Уимблдона&raquo;. Спортсменка хочет встретиться с&nbsp;детьми-сиротами, живущими недалеко от&nbsp;места аварии.</p>\r\n";
$content1 .= "<p>&laquo;Поездка займет несколько дней, поскольку у&nbsp;меня не&nbsp;так много свободного времени. Всего 28&nbsp;дней в&nbsp;году я&nbsp;могу уделить подобным мероприятиям. Поездка в&nbsp;Чернобыль&nbsp;&mdash; только начало моей деятельности. Я&nbsp;хочу помочь детям, пострадавшим от&nbsp;катастрофы, и&nbsp;посмотреть прямо сейчас, как&nbsp;идет строительство больниц и&nbsp;реабилитационных центров&raquo;.</p>\r\n";
$content1 .= "<p>&laquo;Жестоко, что&nbsp;они&nbsp;не&nbsp;имеют родителей,&nbsp;&mdash; добавила Шарапова.&nbsp;&mdash; Мои мама и&nbsp;папа очень сильно помогли мне в&nbsp;жизни, постоянно окружая меня заботой&raquo;.</p>\r\n";
$content1 .= "<p>В&nbsp;2004&nbsp;году после победы в&nbsp;чемпионской гонке WTA&nbsp;Мария получила автомобиль стоимостью $56 тыс. Эти деньги теннисистка пожертвовала в&nbsp;фонд помощи погибшим заложникам в&nbsp;бесланской школе. Кроме того, когда Шарапова стала послом доброй воли ООН, она пожертвовала $100 тыс., которые пошли на&nbsp;строительство различных учреждений для&nbsp;детей, пострадавших от&nbsp;чернобыльской катастрофы.</p>";

$content2  = '<p>Неправильно набран адрес, или&nbsp;такой страницы на&nbsp;сайте не&nbsp;существует. Если вы&nbsp;видите, что&nbsp;на&nbsp;сайте есть неработающая ссылка, пожалуйста, сообщите нам об&nbsp;этом.</p>';

$frontData = array(
    '/' => array(
        array('title' => 'Содержание', 'tag' => 'html', App_Cms_Front_Data_ContentType::getPri() => 'text', 'content' => $content1),
    ),
    '/not-found/' => array(
        array('title' => 'Содержание', 'tag' => 'html', App_Cms_Front_Data_ContentType::getPri() => 'text', 'content' => $content2)
    )
);


// Create tables

$sqlTables = file_get_contents('tables.sql');
$sqlTables = str_replace('~db prefix~', DB_PREFIX, $sqlTables);

foreach (explode(';', $sqlTables) as $query) {
    if (trim($query)) {
        App_Db::get()->execute($query);
    }
}

// App_Db::get()->multiExecute($sqlTables);


// Insert start entries
// Sections

$backSectionObjs = array();
foreach ($backSections as $i) {
    $obj = App_Cms_Back_Section::createInstance();

    $obj->fillWithData($i);
    $obj->isPublished = !isset($i['is_published']) || $i['is_published'];
    $obj->create();

    $backSectionObjs[$obj->getId()] = $obj;
}

$result['Back Sections'] = count($backSectionObjs);


// Users and user to section links

$backUserObjs = array();
foreach ($backUsers as $i) {
    $obj = App_Cms_Back_User::createInstance();
    $obj->fillWithData($i);
    $obj->setPassword($i['passwd']);

    if (!isset($i['status_id'])) {
        $obj->statusId = 1;
    }

    $obj->create();

    $backUserObjs[$obj->getId()] = $obj;
    foreach (array_keys($backSectionObjs) as $j) {
        $link = App_Cms_Back_User_Has_Section::createInstance();
        $link->backUserId = $obj->getId();
        $link->backSectionId = $j;
        $link->create();
    }
}

$result['Back Users'] = count($backUserObjs);


// Controllers

$frontControllerObjs = array();
foreach ($frontControllers as $key => $i) {
    $obj = App_Cms_Front_Controller::createInstance();
    $obj->fillWithData($i);
    $obj->isPublished = true;
    $obj->create();

    $frontControllerObjs[$key] = $obj;
}

$result['Controllers'] = count($frontControllerObjs);


// Templates

$templatesObjs = array();
foreach ($templates as $key => $i) {
    $obj = App_Cms_Front_Template::createInstance();
    $obj->fillWithData($i);
    $obj->isPublished = true;
    $obj->isMultiple = !empty($i['is_multiple']);
    $obj->isDocumentMain = !empty($i['is_document_main']);
    $obj->create();

    $templatesObjs[$key] = $obj;
}

$result['Templates'] = count($templatesObjs);


// Navigation

$frontNavigationObjs = array();
foreach ($frontNavigations as $key => $i) {
    $obj = App_Cms_Front_Navigation::createInstance();
    $obj->fillWithData($i);
    $obj->isPublished = !isset($i['is_published']) || $i['is_published'];
    $obj->create();

    $frontNavigationObjs[$key] = $obj;
}

$result['Navigation'] = count($frontNavigationObjs);


// Documents

$frontDocumentObjs = array();
foreach ($frontDocuments as $level) {
    foreach ($level as $uri => $i) {
        $obj = App_Cms_Front_Document::createInstance();
        $obj->fillWithData($i);
        $obj->isPublished = true;

        if (isset($i['сontroller']) && isset($frontControllerObjs[$i['сontroller']])) {
            $obj->frontControllerId = $frontControllerObjs[$i['сontroller']]->getId();
        }

        if (isset($i['template']) && isset($templatesObjs[$i['template']])) {
            $obj->frontTemplateId = $templatesObjs[$i['template']]->getId();
        }

        $parentUri = str_replace($i['folder'] . '/', '', $uri);
        if (isset($frontDocumentObjs[$parentUri])) {
            $obj->parentId = $frontDocumentObjs[$parentUri]->getId();
        }

        $obj->create();
        $frontDocumentObjs[$uri] = $obj;

        if (isset($i['navigations']) && is_array($i['navigations'])) {
            $links = array();
            foreach ($i['navigations'] as $j) {
                if (isset($frontNavigationObjs[$j])) {
                    array_push($links, $frontNavigationObjs[$j]->getId());
                }
            }

            if ($links) {
                $obj->updateLinks('navigations', $links);
            }
        }
    }
}

$result['Documents'] = count($frontDocumentObjs);


// Data content type

$frontDataContentTypeObjs = array();
foreach ($frontDataContentType as $id => $i) {
    $obj = App_Cms_Front_Data_ContentType::createInstance();
    $obj->fillWithData($i);
    $obj->id = $id;
    $obj->isPublished = true;
    $obj->create();

    $frontDataContentTypeObjs[$obj->getId()] = $obj;
}

$result['Data content type'] = count($frontDataContentTypeObjs);


// Document data

$frontDataObjs = array();
foreach ($frontData as $uri => $blocks) {
    if (isset($frontDocumentObjs[$uri])) {
        foreach ($blocks as $i) {
            $obj = App_Cms_Front_Data::createInstance();
            $obj->fillWithData($i);
            $obj->authStatusId = App_Cms_User::AUTH_GROUP_ALL;
            $obj->frontDocumentId = $frontDocumentObjs[$uri]->getId();
            $obj->isPublished = true;
            $obj->isMount = true;

            if (
                isset($i['сontroller']) &&
                isset($frontControllerObjs[$i['сontroller']])
            ) {
                $obj->frontControllerId = $frontControllerObjs[$i['сontroller']]->getId();
            }

            $obj->create();
            $frontDataObjs[$obj->getId()] = $obj;
        }
    }

}

$result['Data'] = count($frontDataObjs);


// Сообщение о результате

echo '<p>Таблицы в базу данных добавлены:</p><pre>';
print_r($result);
echo '</pre>';

echo '<p>Для доступа <a href="/cms/">в систему управления</a> используйте логин ';
echo '<b><code>' . $backUsers[0]['login'] . '</code></b> ';
echo 'и пароль <b><code>' . $backUsers[0]['passwd'] . '</code></b>.</p>';

$isError = false;
$permissions = array(array(DATA_CONTROLLERS, true),
                     array(DOCUMENT_CONTROLLERS, true),
                     array(DOCUMENT_ROOT . 'f/', true));

foreach ($permissions as $path) {
    if (!system('chmod ' . ($path[1] ? '-R ' : '') . '0777 ' . $path[0])) {
        $isError = true;
    }
}

if ($isError) {
    echo '<p style="color: #c00;">Не забудьте установить права:</p>';
    foreach ($permissions as $path) {
        echo '<code>chmod ';

        if ($path[1]) {
            echo '-R ';
        }

        echo '0777 ' . $path[0] . '</code><br>';
    }

} else if ($permissions) {
    echo '<p>Нужные права на файлы установлены.</p>';
}

if (system('rm -rf ' . dirname(__FILE__))) {
    echo '<p>Установочные файлы удалены.</p>';

} else {
    echo '<p style="color: #c00;">Не забудьте удалить файлы установки:</p>';
    echo '<code>rm -rf ' . dirname(__FILE__) . '/</code>';
}
