<?php

require_once realpath(dirname(__FILE__) . '/../../../core/src') . '/libs.php';

initSettings();
$result = array();

global $gSiteTitle, $gIsUsers;

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
    array('title' => 'Разработчик', 'login' => 'developer', 'passwd' => \Ext\String::getRandomReadable(8), 'email' => 'support@sitedev.ru')
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
    array('/' => array('title' => $gSiteTitle,
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

$content2  = '<p>Такой страницы не&nbsp;существует. Если вы заметили на&nbsp;сайте неработающую ссылку, пожалуйста, <a href="mailto:support@sitedev.ru">сообщите нам</a> об&nbsp;этом.</p>';

$frontData = array(
    '/' => array(
        array('title' => 'Содержание', 'tag' => 'html', \App\Cms\Front\Data\ContentType::getPri() => 'text', 'content' => $content1),
    ),
    '/not-found/' => array(
        array('title' => 'Содержание', 'tag' => 'html', \App\Cms\Front\Data\ContentType::getPri() => 'text', 'content' => $content2)
    )
);


// Create tables

$sqlTables = file_get_contents(dirname(__FILE__) . '/' . 'tables.sql');
$sqlTables = str_replace('~db prefix~', \Ext\Db::get()->getPrefix(), $sqlTables);

foreach (explode(';', $sqlTables) as $query) {
    if (trim($query)) {
        \Ext\Db::get()->execute($query);
    }
}


// Insert start entries
// Sections

$backSectionObjs = array();
foreach ($backSections as $i) {
    $obj = \App\Cms\Back\Section::createInstance();

    $obj->fillWithData($i);
    $obj->isPublished = !isset($i['is_published']) || $i['is_published'];
    $obj->create();

    $backSectionObjs[$obj->getId()] = $obj;
}

$result['Back Sections'] = count($backSectionObjs);


// Users and user to section links

$backUserObjs = array();
foreach ($backUsers as $i) {
    $obj = \App\Cms\Back\User::createInstance();
    $obj->fillWithData($i);
    $obj->setPassword($i['passwd']);

    if (!isset($i['status_id'])) {
        $obj->statusId = 1;
    }

    $obj->create();

    $backUserObjs[$obj->getId()] = $obj;
    foreach (array_keys($backSectionObjs) as $j) {
        $link = \App\Cms\Back\User\Has\Section::createInstance();
        $link->backUserId = $obj->getId();
        $link->backSectionId = $j;
        $link->create();
    }
}

$result['Back Users'] = count($backUserObjs);


// Controllers

$frontControllerObjs = array();
foreach ($frontControllers as $key => $i) {
    $obj = \App\Cms\Front\Controller::createInstance();
    $obj->fillWithData($i);
    $obj->isPublished = true;
    $obj->create();

    $frontControllerObjs[$key] = $obj;
}

$result['Controllers'] = count($frontControllerObjs);


// Templates

$templatesObjs = array();
foreach ($templates as $key => $i) {
    $obj = \App\Cms\Front\Template::createInstance();
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
    $obj = \App\Cms\Front\Navigation::createInstance();
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
        $obj = \App\Cms\Front\Document::createInstance();
        $obj->fillWithData($i);
        $obj->isPublished = true;

        if (!empty($gIsUsers)) {
            $obj->authStatusId = \App\Cms\User::AUTH_GROUP_ALL;
        }

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
                    $links[] = $frontNavigationObjs[$j]->getId();
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
    $obj = \App\Cms\Front\Data\ContentType::createInstance();
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
            $obj = \App\Cms\Front\Data::createInstance();
            $obj->fillWithData($i);
            $obj->frontDocumentId = $frontDocumentObjs[$uri]->getId();
            $obj->isPublished = true;
            $obj->isMount = true;

            if (!empty($gIsUsers)) {
                $obj->authStatusId = \App\Cms\User::AUTH_GROUP_ALL;
            }

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

$nl = PHP_EOL;
$para = $nl . $nl;

// echo 'Таблицы в базу данных добавлены:';
// echo $nl;

// print_r($result);
// echo $para;

echo 'Таблицы в базу данных добавлены.';
echo $para;


echo 'Для доступа в систему управления (/cms/) используйте логин ';
echo $backUsers[0]['login'] . ' и ' . $backUsers[0]['passwd'] . '.';
echo $para;


$isError = false;
$errorLogFile = SETS . 'error.log';

$permissions = array(
    array(CONTROLLERS,           false),
    array(CONTROLLERS . '*',     false),
    array(HELPERS,               false),
    array(HELPERS . '*',         false),
    array(TEMPLATES,             false),
    array(TEMPLATES . '*',       false),
    array(DOCUMENT_ROOT . 'f',   false),
    array(WD . 'cache',          false),
    array(SETS,                  false)
);

if (is_file($errorLogFile)) {
    $permissions[] = array($errorLogFile, false);

} else {
    if (\Ext\File::write($errorLogFile, '', true) === false) {
        echo 'Нужно создать лог-файл ошибок:';
        echo $nl;
        echo $errorLogFile;
        echo $para;

        $permissions[] = array($errorLogFile, false);
    }
}

foreach ($permissions as $path) {
    if (system('chmod ' . ($path[1] ? '-R ' : '') . '0777 ' . $path[0]) === false) {
        $isError = true;
    }
}

// if ($isError) {
    echo 'Проверьте наличие прав на запись:';
    echo $nl;

    foreach ($permissions as $path) {
        echo 'chmod' . (empty($path[1]) ? '' : ' -R') . ' 0777 ' . $path[0];
        echo $nl;
    }

    echo $nl;

// } else if ($permissions) {
//     echo 'Нужные права на файлы установлены.';
//     echo $para;
// }


$solt = '$2y$09$d42R0c216184E3f0tjm60c';
$passwordConsts = array();

if (
    !defined('\App\Cms\User::SECRET') ||
    \App\Cms\User::cryptPassword('test') == '' ||
    \App\Cms\User::SECRET == $solt
) {
    $passwordConsts[] = '\App\Cms\User::SECRET';
}

if (
    !defined('\App\Cms\Back\User::SECRET') ||
    \App\Cms\Back\User::cryptPassword('test') == '' ||
    \App\Cms\Back\User::SECRET == $solt
) {
    $passwordConsts[] = '\App\Cms\Back\User::SECRET';
}

if (count($passwordConsts) > 0) {
    echo 'Не забудьте поменять соль паролей ';
    echo implode(', ', $passwordConsts);
    echo '.';
    echo $para;
}
