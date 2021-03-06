<?php

define('WD', realpath(dirname(__FILE__) . '/../..') . '/');
define('CORE_PATH', WD . 'core/');

define('DOCUMENT_ROOT', WD . 'www/');
define('CORE_DOCUMENT_ROOT', CORE_PATH . 'www/');

define('SETS', WD . 'sets/');
define('CORE_SETS', CORE_PATH . 'sets/');

define('TEMPLATES', WD . 'templates/');
define('CORE_TEMPLATES', CORE_PATH . 'templates/');

define('LIBRARIES', WD . 'src/');
define('CORE_LIBRARIES', CORE_PATH . 'src/');

define('MODELS', WD . 'Model/');

define('HELPERS', LIBRARIES . 'Helper/');
define('CORE_HELPERS', CORE_LIBRARIES . 'Helper/');

define('CONTROLLERS', LIBRARIES . 'Controller/');
define('CORE_CONTROLLERS', CORE_LIBRARIES . 'Controller/');

define('MODULES', WD . 'modules/');
define('CORE_MODULES', WD . 'core-modules/');


/**
 * Файлы классов могут находиться в папках ~/core/src, ~/src.
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
 * Когда используется namespace класс может находится в одноименной папке,
 * поэтому $localPaths содержит два значения.
 *
 * @param string $_class
 */
function __autoload($_class)
{
    $path = preg_split('~[\\\]~', $_class);
//    $include = array(LIBRARIES, MODELS, CONTROLLERS, HELPERS, MODULES);
    $include = array(LIBRARIES, MODULES);
//    $core = array(CORE_LIBRARIES, CORE_CONTROLLERS, CORE_HELPERS, CORE_MODULES);
    $core = array(CORE_LIBRARIES, CORE_MODULES);

    if ($path[0] == 'Core') {
        $include = $core;
        unset($path[0]);
        $path = array_values($path);

    } else if ($path[0] != 'App') {
        $include = array_merge($include, $core);
    }

//    $l = count($path) - 1;
//    $filename = preg_replace('/([^_])(Controller|Helper)$/', '$1', $path[$l]);
//    $path[$l] = $filename;
//    $path[] = $filename . '.php';

    $path[] = $path[count($path) - 1] . '.php';
    $localPaths[] = implode(DIRECTORY_SEPARATOR, $path);

    unset($path[count($path) - 2]);
    $localPaths[] = implode(DIRECTORY_SEPARATOR, $path);

    foreach ($include as $dir) {
        foreach ($localPaths as $localPath) {
            if (is_file($dir . $localPath)) {
                require_once $dir . $localPath;
                break;
            }
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
    call_user_func_array(array('\Ext\Lib', 'd'), $args);
}

function debug($_var)
{
    $args = func_get_args();
    call_user_func_array(array('\Ext\Lib', 'debug'), $args);
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
    $url = \Ext\File::parseUrl();
    goToUrl(rtrim($url['path'], '/') . '/' . $_append);
}

function documentNotFound()
{
    global $gIsHidden;

    header('HTTP/1.0 404 Not Found');

    if (class_exists('\App\Cms\Front\Document')) {
        $realUrl = \Ext\File::parseUrl();
        $document = \App\Cms\Front\Document::load(
            getLangInnerUri() . 'not-found/',
            'uri'
        );

        if ($document) {
            if ($document->link && $document->link != $realUrl['path']) {
                goToUrl($document->link);

            } else if (
                $document->getController() && (
                    $document->isPublished == 1 || !empty($gIsHidden)
                )
            ) {
                $controller = \App\Cms\Front\Document::initController(
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
                $timeTaken = \Ext\Number::format($time / 3600, 2) . ' hours';

            } else if ($time > 60) {
                $timeTaken = \Ext\Number::format($time / 60, 2) . ' minutes';

            } else {
                $timeTaken = \Ext\Number::format($time, 6) . ' seconds';
            }

            if ($item['level']) {
                for ($i = 0; $i < $item['level']; $i++) {
                    $result .= $lv;
                }
            }

            $result .= ($item['label'] ? "{$item['label']} ($name)" : $name) .
                       ': ' . $timeTaken;

            if ($globalTime && $globalTime != $time) {
                $result .= ' (' . \Ext\Number::format(($time * 100) / $globalTime, 2) . '%)';
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
