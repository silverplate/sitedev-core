<?php

define('WD', realpath(dirname(__FILE__) . '/../..') . '/');
define('CORE_PATH', WD . 'core/');

define('DOCUMENT_ROOT', WD . 'www/');
define('CORE_DOCUMENT_ROOT', CORE_PATH . 'www/');

define('SETS', WD . 'sets/');
define('CORE_SETS', CORE_PATH . 'sets/');

define('TEMPLATES', WD . 'templates/');
define('CORE_TEMPLATES', CORE_PATH . 'templates/');

define('LIBRARIES', WD . 'library/');
define('CORE_LIBRARIES', CORE_PATH . 'library/');

define('MODELS', WD . 'models/');

define('HELPERS', WD . 'helpers/');
define('CORE_HELPERS', CORE_PATH . 'helpers/');

define('CONTROLLERS', WD . 'controllers/');
define('CORE_CONTROLLERS', CORE_PATH . 'controllers/');

define('MODULES', WD . 'modules/');
define('CORE_MODULES', WD . 'core-modules/');


/**
 * Файлы классов могут находиться в папках ~/core/library, ~/library, ~/models.
 *
 * В папках ~/core-modules (базовый функционал) и ~/modules (реализация
 * на проекте) размещаются модули СУ.
 *
 * Префикс Core в названии класса указавает на то, что класс из ядра СУ и файл
 * с классом должен находиться внутри папки ~/core.
 *
 * Префикс Cms указывает на то, что класс нужен для функционирования СУ и файл
 * находится в подпапке Cms, которая может быть как внутри core, так и нет.
 *
 * Префикс App говорит о том, что класс переопределяет один из классов ядра,
 * и в нем может быть уникальный для сайта функционал. Класса с таким префиксом
 * в ядре быть не может.
 *
 * Класс без перечисленных префиксов является единственным для системы, ничего
 * не переопределяет и может находится как в ядре (например, внешняя библиотека
 * PhpMailer), так и только на сайте.
 *
 * @param string $_class
 */
function __autoload($_class)
{
    $path = preg_split('~[_\\\]~', $_class);
    $include = array(LIBRARIES, MODELS, CONTROLLERS, HELPERS, MODULES);
    $core = array(CORE_LIBRARIES, CORE_CONTROLLERS, CORE_HELPERS, CORE_MODULES);

    if ($path[0] == 'Core') {
        $include = $core;
        unset($path[0]);
        $path = array_values($path);

    } else if ($path[0] != 'App') {
        $include = array_merge($include, $core);
    }

    $l = count($path) - 1;
    $filename = preg_replace('/([^_])(Controller|Helper)$/', '$1', $path[$l]);
    $path[$l] = $filename . '.php';
    $localPath = implode(DIRECTORY_SEPARATOR, $path);

    foreach ($include as $dir) {
        if (is_file($dir . $localPath)) {
            require_once $dir . $localPath;
            break;
        }
    }
}

function initSettings()
{
    require_once CORE_SETS . 'project.php';

    if (is_file(SETS . 'local.php')) {
        require_once SETS . 'local.php';
    }

    require_once SETS . 'project.php';
}

function getLangInnerUri()
{
    global $gSiteLang;
    return empty($gSiteLang) ? '/' : "/$gSiteLang/";
}

function d()
{
    $args = func_get_args();
    $count = count($args);

    if ($count == 1) {
        debug($args[0]);

    } else {
        foreach ($args as $i => $var) {
            if ($i != 0) echo PHP_EOL;
            echo $i + 1 . ':';
            echo PHP_EOL;

            debug($var);
        }
    }

    die();
}

function debug($_var)
{
    if (PHP_SAPI == 'cli') {
        print_r($_var);
        echo PHP_EOL;

    } else {
        echo '<pre>';

        if (is_string($_var))   echo htmlspecialchars($_var);
        else                    print_r($_var);

        echo '</pre>';
        echo PHP_EOL;
    }
}

function goToUrl($_url)
{
    redirect($_url);
}

function redirect($_url)
{
    header('Location: ' . $_url);
    exit;
}

function reload($_append = null)
{
    $url = Ext_File::parseUrl();
    goToUrl(rtrim($url['path'], '/') . '/' . $_append);
}

function documentNotFound()
{
    global $gIsHidden;

    header('HTTP/1.0 404 Not Found');

    if (class_exists('App_Cms_Front_Document')) {
        $realUrl = Ext_File::parseUrl();
        $document = App_Cms_Front_Document::load(getLangInnerUri() . 'not-found/', 'uri');

        if ($document) {
            if ($document->link && $document->link != $realUrl['path']) {
                goToUrl($document->link);

            } else if (
                $document->getController() && (
                    $document->isPublished == 1 || !empty($gIsHidden)
                )
            ) {
                $controller = App_Cms_Front_Document::initController(
                    $document->getController(),
                    $document
                );

                $controller->execute();
                $controller->output();
                exit();
            }
        }
    }

    echo '<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1>';
    echo "<p>The requested URL {$_SERVER['REQUEST_URI']} was not found on this server.</p><hr />";
    echo "<i>{$_SERVER['SERVER_SOFTWARE']} at {$_SERVER['SERVER_NAME']} Port {$_SERVER['SERVER_PORT']}</i>";
    echo '</body></html>';
    exit();
}

function traceTime($_function, $_label = null)
{
    global $gReceptacle;

    if (!$gReceptacle) $gReceptacle = array();

    list($msec, $sec) = explode(' ', microtime());
    $now = ((float) $msec + (float) $sec);
    $i = 0;

    while (true) {
        $func = $i == 0 ? $_function : "$_function \{$i\}";

        if (!isset($gReceptacle[$func])) {
            $open = $isGlobal = false;
            $level = 0;

            foreach ($gReceptacle as $key => $value) {
                if ($value['is_global']) {
                    $isGlobal = true;
                }

                if (is_null($value['finish'])) {
                    $level++;
                    if (!$open) $open = $key;
                }
            }

            if (!$isGlobal && $open) {
                $gReceptacle[$open]['is_global'] = true;
            }

            $gReceptacle[$func] = array(
                'start' => $now,
                'finish' => null,
                'label' => $_label,
                'is_global' => false,
                'level' => $level
            );

            break;

        } else if (!$gReceptacle[$func]['finish']) {
            $gReceptacle[$func]['finish'] = $now;

            if ($_label) {
                $gReceptacle[$func]['label'] = $_label;
            }

            break;

        } else {
            $i++;
        }
    }
}

function traceTimeGetReport($_format = 'html')
{
    global $gReceptacle;

    $result = '';
    $nl = $_format != 'html' ? PHP_EOL : '<br>';
    $lv = $_format != 'html' ? "\t" : '&bull;&nbsp;';

    if ($gReceptacle) {
        $globalTime = null;

        foreach ($gReceptacle as $name => $item) {
            if ($item['is_global']) {
                $globalTime = $item['finish'] - $item['start'];
                break;
            }
        }

        foreach ($gReceptacle as $name => $item) {
            $time = $item['finish'] - $item['start'];

            if ($time > 3600) {
                $timeTaken = Ext_Number::format($time / 3600, 2) . ' hours';

            } else if ($time > 60) {
                $timeTaken = Ext_Number::format($time / 60, 2) . ' minutes';

            } else {
                $timeTaken = Ext_Number::format($time, 6) . ' seconds';
            }

            if ($item['level']) {
                for ($i = 0; $i < $item['level']; $i++) {
                    $result .= $lv;
                }
            }

            $result .= ($item['label'] ? "{$item['label']} ($name)" : $name) .
                       ': ' . $timeTaken;

            if ($globalTime && $globalTime != $time) {
                $result .= ' (' . Ext_Number::format(($time * 100) / $globalTime, 2) . '%)';
            }

            $result .= $nl;
        }
    }

    return $result;
}

function isWindows()
{
    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}
