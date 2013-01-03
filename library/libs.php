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

define('DATA_CONTROLLERS', LIBRARIES . 'App/Cms/Front/Data/Controller/');
define('DOCUMENT_CONTROLLERS', LIBRARIES . 'App/Cms/Front/Document/Controller/');


/**
 * Файлы классов могут находиться в папках ~/core/library, ~/library, ~/models.
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
    $path = explode('_', $_class);
    $include = array(MODELS, LIBRARIES);
    $core = array(CORE_LIBRARIES);

    if ($path[0] == 'Core') {
        $include = $core;
        unset($path[0]);
        $path = array_values($path);

    } else if ($path[0] != 'App') {
        $include = array_merge($include, $core);
    }

    $path[count($path) - 1] = $path[count($path) - 1] . '.php';
    $localPath = implode('/', $path);

    foreach ($include as $dir) {
        if (is_file($dir . $localPath)) {
            require_once $dir . $localPath;
            break;
        }
    }
}

function get_lang_inner_uri() {
    return defined('SITE_LANG') && SITE_LANG ? '/' . SITE_LANG . '/' : '/';
}

function send_email($_mail_pref, $_email, $_subject, $_body, $_is_html = false, $_useEnv = true)
{
    global $gAdminEmails;

    $env = defined('ENV') ? ENV : 'staging';

    if ($_useEnv && $env == 'development') {
        return false;
    }

    if (is_array($_mail_pref) && !empty($_mail_pref['from'])) {
        $mailer = new phpmailer();
        $mailer->IsMail();
        $mailer->IsHTML($_is_html);
        $mailer->CharSet = 'windows-1251';
        $mailer->From = $_mail_pref['from'];
        $mailer->Sender = $mailer->From;
        $mailer->Subject = $_subject;
        $mailer->Body = $_body;

        if (isset($_mail_pref['signature'])) {
            $mailer->Body .= $_mail_pref['signature'];
        }

        if (isset($_mail_pref['from_name'])) {
            $mailer->FromName = $_mail_pref['from_name'];
        }

        if (isset($_mail_pref['subject'])) {
            $mailer->Subject = $_mail_pref['subject'] . $mailer->Subject;
        }

        if (isset($_mail_pref['bcc'])) {
            foreach (Ext_String::split($_mail_pref['bcc']) as $item) {
                $mailer->AddBCC($item);
            }
        }

        $isEmail = false;
        $emails = is_array($_email) ? $_email : array($_email);

        foreach ($emails as $email) {
            if (Ext_String::isEmail($email)) {
                $isEmail = true;
                $mailer->AddAddress($email);
            }
        }

        if ($_useEnv && $env == 'staging') {
            if (empty($gAdminEmails)) {
                return false;
            }

            $system_body = '<p>Письмо направлено:</p>';

            for ($i = 0; $i < count($emails); $i++) {
                $system_body .= $emails[$i];

                if (count($emails) != $i + 1) {
                    $system_body .= ', ';
                }
            }

            $mailer->ClearAllRecipients();

            foreach ($gAdminEmails as $adminEmail) {
                $mailer->addAddress($adminEmail);
            }

            $mailer->Body =
                '<p>' . $system_body . '</p>' .
                '<p>Оригинал:</p>' .
                ($mailer->ContentType == 'text/plain' ? nl2br($mailer->Body) : $mailer->Body);

            $mailer->IsHTML(true);
        }

        foreach (array('FromName', 'Subject', 'Body') as $name) {
            $mailer->$name = @iconv('utf-8', 'windows-1251', $mailer->$name);
        }

        return $isEmail ? $mailer->Send() : false;
    }

    return false;
}

function getAdvParams()
{
    return array('utm_source', 'adv');
}

function getAdvMailParams()
{
    $result = array();
    $params = getAdvParams();
    $params[] = 'HTTP_REFERER';

    foreach ($params as $item) {
        $value = advGetCookie($item);

        if (!empty($value)) {
            $result[$item] = $value;
        }
    }

    return $result;
}

function advMonitor()
{
    foreach (getAdvParams() as $item) {
        if (!empty($_GET[$item])) {
            advSetCookie($item, $_GET[$item]);
        }
    }

    $envName = 'HTTP_REFERER';
    if (!empty($_SERVER[$envName])) {
        $referer = strtolower($_SERVER[$envName]);
        $url = parse_url($referer);

        if (
            !empty($url['host']) &&
            !empty($_SERVER['HTTP_HOST']) &&
            $url['host'] != strtolower($_SERVER['HTTP_HOST'])
        ) {
            $prev = advGetCookie($envName);
            if (!$prev || $prev != $referer) {
                advSetCookie($envName, $referer);
            }
        }
    }
}

function advSetCookie($_name, $_value)
{
    $name = 'adv_' . strtolower($_name);
    $_COOKIE[$name] = $_value;
    setcookie($name, $_value, 0, '/', '.' . $_SERVER['HTTP_HOST']);
}

function advGetCookie($_name)
{
    $name = 'adv_' . strtolower($_name);
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
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
//     $uri = empty($_SERVER['REQUEST_URI'])
//          ? './'
//          : preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
//
//     goToUrl($uri . $_append);

    goToUrl('./' . $_append);
}

function documentNotFound()
{
    header('HTTP/1.0 404 Not Found');

    if (class_exists('App_Cms_Front_Document')) {
        $realUrl = parse_url($_SERVER['REQUEST_URI']);
        $document = App_Cms_Front_Document::load(get_lang_inner_uri() . 'not-found/', 'uri');

        if ($document) {
            if ($document->link && $document->link != $realUrl['path']) {
                goToUrl($document->link);

            } else if (
                $document->getController() && (
                    $document->is_published == 1 ||
                    (defined('IS_HIDDEN') && IS_HIDDEN)
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
