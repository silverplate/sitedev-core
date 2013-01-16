<?php

if (is_file(SETS . 'local.php')) {
    require_once SETS . 'local.php';
}

date_default_timezone_set('Etc/GMT-4');

ini_set('default_charset', 'utf-8');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('error_reporting', E_ALL);
ini_set('magic_quotes_gpc', 0);

define('SITE_KEY', 'sitekey');
define('SITE_TITLE', 'SiteDev');

if (!defined('HTTP_HOST')) {
    define(
        'HTTP_HOST',
        empty($_SERVER['HTTP_HOST']) ? 'sitedev.ru' : $_SERVER['HTTP_HOST']
    );
}

if (!defined('SITE_URL')) {
    define('SITE_URL', 'http://' . HTTP_HOST);
}

if (!defined('IS_CACHE')) {
    define('IS_CACHE', false);
}

if (!defined('IS_APC')) {
    define('IS_APC', true);
}

define('IS_USERS', false);
define('DB_PREFIX', '');

if (defined('ENV')) {
    $env = ENV;

} else {
    $env = null;
    $developmentSites = array('sitedev');
    $stagingSites = array('dev.sitedev.ru');
    $productionSites = array('sitedev.ru', 'www.sitedev.ru');

    if (empty($_SERVER['HTTP_HOST'])) {
        if (!empty($_SERVER['PWD'])) {
            $script = $_SERVER['PWD'];
            if (!empty($_SERVER['SCRIPT_FILENAME'])) {
                $script . '/' . $_SERVER['SCRIPT_FILENAME'];
            }

            foreach ($stagingSites as $item) {
                if (strpos($script, '/' . $item . '/') !== false) {
                    $env = 'staging';
                    break;
                }
            }

            if (empty($env)) {
                foreach ($developmentSites as $item) {
                    if (strpos($script, '/' . $item . '/') !== false) {
                        $env = 'development';
                        break;
                    }
                }
            }

            if (empty($env)) {
                foreach ($productionSites as $item) {
                    if (strpos($script, '/' . $item . '/') !== false) {
                        $env = 'production';
                        break;
                    }
                }
            }
        }

    } else if (in_array(strtolower($_SERVER['HTTP_HOST']), $developmentSites)) {
        $env = 'development';

    } else if (in_array(strtolower($_SERVER['HTTP_HOST']), $stagingSites)) {
        $env = 'staging';

    } else if (in_array(strtolower($_SERVER['HTTP_HOST']), $productionSites)) {
        $env = 'production';
    }
}

switch ($env) {
    case 'development':
        if (!defined('DB_CONNECTION_STRING')) {
            define('DB_CONNECTION_STRING', 'mysql://user:password@host/dbname/');
        }

        ini_set('display_errors', 1);
        break;

    case 'staging':
        if (!defined('DB_CONNECTION_STRING')) {
            define('DB_CONNECTION_STRING', 'mysql://user:password@host/dbname/');
        }

        ini_set('display_errors', 1);
        break;

    case 'production':
        if (!defined('DB_CONNECTION_STRING')) {
            define('DB_CONNECTION_STRING', 'mysql://user:password@host/dbname/');
        }

        ini_set('display_errors', 0);
        break;

    default:
        throw new Exception('Unknown site');
}

if (!defined('ENV')) {
    define('ENV', $env);
}

$gCustomUrls = array();


// Почта

global $gAdminEmails;

if (!isset($gAdminEmails)) {
    $gAdminEmails = array('support@sitedev.ru');
}

$gMail = array(
    'from' => array('email' => 'support@sitedev.ru', 'name' => SITE_TITLE),
    'subject' => array('append' => SITE_TITLE),
    'signature' => array('text' => "\n\n\n--\nСлужба поддержки\nsupport@sitedev.ru"),
//     'bcc' => array('support@sitedev.ru')
);

$gMail['signature']['html'] = nl2br($gMail['signature']['text']);

$gBackOfficeMail = $gMail;
$gBackOfficeMail['subject']['append'] = $gBackOfficeMail['subject']['append']
                                      ? 'СУ. ' . $gBackOfficeMail['subject']['append']
                                      : 'СУ';


// Загрузка файлов

global $gMaxUploadFilesize, $gAmountMaxUploadFilesize;

if (!isset($gMaxUploadFilesize)) {
    $gMaxUploadFilesize = 1.5;
}

if (!isset($gAmountMaxUploadFilesize)) {
    $gAmountMaxUploadFilesize = (int) ini_get('upload_max_filesize');
}
