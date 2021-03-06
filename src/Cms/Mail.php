<?php

namespace Core\Cms;

abstract class Mail extends PhpMailer
{
    const LOW    = 1;
    const NORMAL = 3;
    const HIGH   = 5;

    protected $_signature;
    protected $_htmlSignature;
    protected $_config;

    public function __construct()
    {
        parent::__construct();

        if (is_null($this->_config)) {
            global $gMail;
            $this->_config = $gMail;
        }

        $this->isMail();
        $this->CharSet  = 'utf-8';
        $this->FromName = $this->_config['from']['name'];
        $this->From     = $this->_config['from']['email'];
        $this->Sender   = $this->_config['from']['email'];

        if (!empty($this->_config['signature'])) {
            if (!empty($this->_config['signature']['text'])) {
                $this->setSignature($this->_config['signature']['text']);
            }

            if (!empty($this->_config['signature']['html'])) {
                $this->setHtmlSignature($this->_config['signature']['html']);
            }
        }
    }

    public function addRecipient($_type, $_recipient)
    {
        $email = null;
        $name = null;

        if (is_array($_recipient)) {
            $email = $_recipient['email'];

            if (!empty($_recipient['name'])) {
                $name = $_recipient['name'];
            }

        } else {
            $email = $_recipient;
        }

        return $this->addAnAddress($_type, $email, $name);
    }

    public function addTo($_recipient)
    {
        return $this->addRecipient('to', $_recipient);
    }

    public function addToCc($_recipient)
    {
        return $this->addRecipient('cc', $_recipient);
    }

    public function addToBcc($_recipient)
    {
        return $this->addRecipient('bcc', $_recipient);
    }

    public function setPriority($_priority)
    {
        if (in_array(
            $_priority,
            array(self::HIGH, self::NORMAL, self::LOW)
        )) {
            $this->Priority = $_priority;
        }
    }

    public function setSignature($_signature)
    {
        $this->_signature = $_signature;
    }

    public function setHtmlSignature($_signature)
    {
        $this->_htmlSignature = $_signature;
    }

    public function isHtmlBody()
    {
        return preg_match('/<[^>]+\/?>/', $this->Body);
    }

    public function send($_ignoreEnv = false, $_appendPreff = true)
    {
        global $gAdminEmails, $gEnv;

        $env = empty($gEnv) ? 'staging' : $gEnv;
        if ($_ignoreEnv) $env = null;

        if (
            $env == 'development' ||
            ($env == 'staging' && empty($gAdminEmails))
        ) {
            return null;

        } else {

            // Добавление в скрытую копию адресов из настроек

            if (!empty($this->_config['bcc'])) {
                $bcc = is_array($this->_config['bcc'])
                     ? $this->_config['bcc']
                     : \Ext\String::split($this->_config['bcc']);

                foreach ($bcc as $recipient) {
                    $this->addBcc($recipient);
                }
            }


            // На staging-сервере оригинал письма рассылается администраторам
            // с указанием кому оно направлялось.

            if ($env == 'staging') {
                $recipients = array();

                foreach ($this->to as $recipient) {
                    $recipients[] = $recipient[0];
                }

                foreach ($this->cc as $recipient) {
                    $recipients[] = $recipient[0];
                }

                foreach ($this->bcc as $recipient) {
                    $recipients[] = $recipient[0];
                }

                $this->clearAllRecipients();

                foreach ($gAdminEmails as $adminEmail) {
                    $this->addTo($adminEmail);
                }

                $body = array(
                    'Получатели:',
                    implode(', ', $recipients),
                    'Оригинальное письмо:',
                    $this->Body
                );

                $this->Body = implode(
                    $this->isHtmlBody() ? '<br><br>' : "\n\n",
                    $body
                );
            }

            if ($_appendPreff) {

                // Заголовок

                if (
                    !empty($this->_config['subject']) &&
                    !empty($this->_config['subject']['append'])
                ) {
                    if ($this->Subject) {
                        $this->Subject .= '. ';
                    }

                    $this->Subject .= $this->_config['subject']['append'];
                }


                // Добавление подписи

                if ($this->isHtmlBody()) {
                    if (!empty($this->_htmlSignature)) {
                        $this->Body .= $this->_htmlSignature;
                    }

                } else if (!empty($this->_signature)) {
                    $this->Body .= $this->_signature;
                }
            }

            $this->isHTML($this->isHtmlBody());

            if (!$this->isHtmlBody()) {
                $this->Body = \Ext\String::replaceEntities($this->Body);
            }


            // Дополнительный отступ, если есть вложения, чтобы в письме они
            // аккуратно показывались и не прилипали к тексту.

            if ($this->attachmentExists() && $this->Body) {
                $this->Body .= $this->isHtmlBody() ? '<br><br>' : "\n\n";
            }

            return parent::send();
        }
    }

    public static function forcePost($_recipient,
                                     $_body,
                                     $_subject = null,
                                     $_appendPreff = false,
                                     $_attachments = null)
    {
        return self::post(
            $_recipient,
            $_body,
            $_subject,
            true,
            is_null($_appendPreff) ? false : $_appendPreff,
            $_attachments
        );
    }

    public static function postAttachments(array $_attachments,
                                           $_recipient,
                                           $_body,
                                           $_subject = null,
                                           $_ignoreEnv = null,
                                           $_appendPreff = null)
    {
        return self::post(
            $_recipient,
            $_body,
            $_subject,
            $_ignoreEnv,
            $_appendPreff,
            $_attachments
        );
    }

    public static function post($_recipient,
                                $_body,
                                $_subject = null,
                                $_ignoreEnv = false,
                                $_appendPreff = true,
                                $_attachments = null)
    {
        $class = get_called_class();
        $postman = new $class;
        $postman->Body = $_body;

        if (!empty($_subject)) {
            $postman->Subject = $_subject;
        }

        if (is_array($_recipient) && !empty($_recipient['to'])) {
            foreach ($_recipient['to'] as $recipient) {
                $postman->addTo($recipient);
            }

            if (!empty($_recipient['cc'])) {
                foreach ($_recipient['cc'] as $recipient) {
                    $postman->addToCc($recipient);
                }
            }

            if (!empty($_recipient['bcc'])) {
                foreach ($_recipient['bcc'] as $recipient) {
                    $postman->addToBcc($recipient);
                }
            }

        } else if (is_array($_recipient)) {
            foreach ($_recipient as $recipient) {
                $postman->addTo($recipient);
            }

        } else {
            $postman->addTo($_recipient);
        }

        if (!empty($_attachments)) {
            foreach ($_attachments as $file) {
                $postman->addAttachment($file['path'], $file['name']);
            }
        }

        return $postman->send(
            is_null($_ignoreEnv) ? false : $_ignoreEnv,
            is_null($_appendPreff) ? true : $_appendPreff
        );
    }
}
