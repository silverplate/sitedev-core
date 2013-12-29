<?php

class Core_Error_Exeption extends \Exception
{
    public function __construct($_num = 0,
                                $_str = null,
                                $_file = null,
                                $_line = 0)
    {
        parent::__construct($_str, $_num);

        $this->file = $_file;
        $this->line = $_line;
    }
}

class Core_Error
{
    const MODE_DEVELOPMENT = 1;
    const MODE_PRODUCTION  = 2;

    protected $_mode;
    protected $_logFile;
    protected $_reportEmails;
    protected $_trace;

    /**
     * @var Core_Error
     */
    protected static $_instance;

    protected static $_prevErrorHandler;
    protected static $_prevExeptionHandler;

    public static function init($_mode, $_logFile = null, $_reportEmails = null)
    {
        $class = get_called_class();
        self::$_instance = new $class;
        self::$_instance->setMode($_mode);

        if (!empty($_logFile)) {
            self::$_instance->_logFile = $_logFile;
        }

        if (!empty($_reportEmails)) {
            self::$_instance->_reportEmails = is_array($_reportEmails)
                                            ? $_reportEmails
                                            : array($_reportEmails);
        }

        error_reporting(E_ALL);
        ini_set('display_errors', 0);

        set_exception_handler(array(self::get(), 'handleException'));
        set_error_handler(array(self::get(), 'handleError'), E_ALL);
        register_shutdown_function(array(self::get(), 'handleShutdown'));
    }

    /**
     * @return Core_Error
     */
    public static function get()
    {
        return self::$_instance;
    }

    public function setMode($_mode)
    {
        $this->_mode = $_mode == self::MODE_DEVELOPMENT
                     ? self::MODE_DEVELOPMENT
                     : self::MODE_PRODUCTION;
    }

    /**
     * @return array|false
     */
    protected function _getError()
    {
        $error = error_get_last();
        return $error && ($error['type'] & E_ALL) ? $error : false;
    }

    /**
     * @param Exception $_e
     * @return array
     */
    protected function _getTrace(Exception $_e)
    {
        return isset($this->_trace) ? $this->_trace : $_e->getTrace();
    }

    public function handleError($_number, $_string, $_file, $_line)
    {
        $this->handleException(new Core_Error_Exeption(
            $_number,
            $_string,
            $_file,
            $_line
        ));
    }

    public function handleShutdown()
    {
        $error = $this->_getError();

        if ($error) {
            $this->_trace = debug_backtrace();

            $this->handleException(new Core_Error_Exeption(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            ));
        }
    }

    public function handleException(Exception $_e)
    {
        if ($this->_mode != self::MODE_DEVELOPMENT) {
            self::logExeption($_e);
        }

        if (empty($_SERVER) || empty($_SERVER['REQUEST_URI'])) {
            $this->_toConsole($_e);

        } else if ($this->_mode == self::MODE_DEVELOPMENT) {
            $this->_toBrowser('Exception', $this->getHtml(
                $_e->getMessage(),
                $_e->getFile(),
                $_e->getLine(),
                $this->getMoreTraceInfo($this->_getTrace($_e)),
                empty($_SERVER['REQUEST_URI']) ? null : $_SERVER['REQUEST_URI'],
                get_class($_e)
            ));

        } else {
            $this->showUserfriendlyMessage();
        }

        exit(1);
    }

    protected function _toConsole($_e)
    {
        $nl = PHP_EOL;
        $result = array('Exception');

        if (!empty($_SERVER) && !empty($_SERVER['REQUEST_URI'])) {
            $result[] = 'Request URI:' . $nl . $_SERVER['REQUEST_URI'];
        }

        $result[] = 'Name:' .    $nl . get_class($_e);
        $result[] = 'Message:' . $nl . $_e->getMessage();
        $result[] = 'File:' .    $nl . $_e->getFile();
        $result[] = 'Line:' .    $nl . $_e->getLine();
        $result[] = 'Trace:' .   $nl . $this->getMoreTraceInfo($this->_getTrace($_e));

        echo $nl . implode($nl . $nl, $result) . $nl . $nl;
    }

    public function getHtml($_msg, $_file, $_line, $_trace, $_uri = null, $_exception = null)
    {
        $html  = '<h1>Exception</h1>';
        $html .= '<dl><dt>Name</dt><dd>' .
                 (empty($_exception) ? 'Exception' : $_exception) .
                 '</dd></dl>';

        if (!empty($_uri)) {
            $html .= "<dl><dt>Request URI</dt><dd>$_uri</dd></dl>";
        }

        $html .= "<dl><dt>Message</dt><dd>$_msg</dd></dl>";
        $html .= "<dl><dt>File</dt><dd>$_file</dd></dl>";
        $html .= "<dl><dt>Line</dt><dd>$_line</dd></dl>";
        $html .= "<dl><dt>Trace</dt><dd><pre>$_trace</pre></dd></dl>";

        return $html;
    }

    public function showUserfriendlyMessage()
    {
        $html  = '<h1>К сожалению, произошла ошибка.</h1>';
        $html .= '<p>Разработчики будут о&nbsp;ней оповещены.</p>';
        $html .= '<p>Если вы хотите дополнительно прокомментировать случившееся, пожалуйста, <a href="mailto:support@sitedev.ru">напишите нам письмо</a>. Любая информация важна для&nbsp;нас и&nbsp;поможет решить проблему. Спасибо.</p>';
        $html .= '<p>Приносим извинения за&nbsp;доставленные неудобства.</p>';

        $this->_toBrowser('Ошибка', $html);
    }

    protected function _toBrowser($_title, $_content)
    {
        echo '<!DOCTYPE html>';
        echo '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8">';
        echo '<style type="text/css">body { font-family: Arial, sans-serif; font-size: 84%; padding: 1em 2em; max-width: 600px; }</style>';
        echo "<title>$_title</title></head><body>$_content</body></html>";
    }

    public function logExeption(Exception $_e)
    {
        $error = $_e->getFile() . ':' . $_e->getLine();
        $items = array(date('Y-m-d H:i:s'), $error);

        if (!empty($_SERVER) && !empty($_SERVER['REQUEST_URI'])) {
            $items[] = $_SERVER['REQUEST_URI'];
        }

        if ($_e->getMessage()) {
            $items[] = $_e->getMessage();
        }

        $items[] = $this->getMoreTraceInfo($this->_getTrace($_e));
        $message = implode(PHP_EOL, $items);

        if ($this->wasEmailed($error) === false) {
            $this->send($message);
        }

        return self::log($message);
    }

    public function log($_message)
    {
        /**
         * @todo Нужно ли вручную добавлять сообщения в системный лог PHP
         * или оно все равно туда попадет?
         *
         * error_log($_message);
         */

        return $this->_logFile
             ? error_log($_message, 3, $this->_logFile)
             : false;
    }

    public function wasEmailed($_error)
    {
        if ($this->_logFile) {
            $matches = array();

            preg_match_all(
                '/^([0-9 :-]{19})\n(.+)$/m',
                file_get_contents($this->_logFile),
                $matches
            );

            if ($matches) {
                $dates = array_reverse($matches[1]);
                $errors = array_reverse($matches[2]);

                for ($i = 0; $i < count($dates); $i++) {
                    $date = strtotime($dates[$i]);

                    if (time() - $date > 1800)       return false;
                    else if ($errors[$i] == $_error) return true;
                }
            }

            return false;
        }

        return null;
    }

    public function send($_message)
    {
        global $gHost;

        if ($this->_reportEmails && $this->_mode == self::MODE_PRODUCTION) {
            $sended = false;
            $subject = "Ошибка $gHost";

            if (class_exists('App_Cms_Mail')) {
                $sended = App_Cms_Mail::forcePost(
                    $this->_reportEmails,
                    $_message,
                    $subject
                );
            }

            if (!$sended) {
                $sended = mail(
                    implode(', ', $this->_reportEmails),
                    $subject,
                    $_message
                );
            }

            return $sended;
        }

        return null;
    }

    public function getMoreTraceInfo($_trace)
    {
        $i = 0;
        $message = '';

        foreach ($_trace as $t) {
            $message .= "#$i ";

            if (!empty($t['file'])) {
                $message .= $t['file'] . ' (' . $t['line'] . '): ';
            }

            if (!empty($t['type'])) {
                $message .= $t['class'] . $t['type'];
            }

            $message .= $t['function'] . '(';

            if (!empty($t['args'])) {
                $j = 0;
                $isMulti = count($t['args']) > 1;
                if ($isMulti) $message .= PHP_EOL;

                foreach ($t['args'] as $arg) {
                    if ($isMulti) $message .= '  ';

                    if (is_object($arg)) {
                        $message .= 'object of class "' . get_class($arg) . '"';

                    } else if (is_resource($arg)) {
                        $message .= 'resource type "' . get_resource_type($arg) . '"';

                    } else if (is_array($arg)) {
                        $message .= 'Array(' . count($arg) . ')';

                    } else if (\Ext\Number::isNumber($arg)) {
                        $message .= $arg;

                    } else {
                        $message .= '"' . $arg . '"';
                    }

                    $j++;

                    if ($isMulti) {
                        if ($j != count($t['args'])) $message .= ',';
                        $message .= PHP_EOL;
                    }
                }
            }

            $message .= ')' . PHP_EOL;
            $i++;
        }

        return $message;
    }

    public static function blank() {}

    public static function ignoreErrors()
    {
        $handler = array(get_called_class(), 'blank');
        self::$_prevErrorHandler = set_error_handler($handler);
        self::$_prevExeptionHandler = set_exception_handler($handler);
    }

    public static function restoreHandlers()
    {
        if (!empty(self::$_prevErrorHandler)) {
            set_error_handler(self::$_prevErrorHandler);
        }

        if (!empty(self::$_prevExeptionHandler)) {
            set_exception_handler(self::$_prevExeptionHandler);
        }
    }
}
