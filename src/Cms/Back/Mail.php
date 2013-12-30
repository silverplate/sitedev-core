<?php

namespace Core\Cms\Back;

abstract class Mail extends \Core\Cms\Mail
{
    public function __construct()
    {
        global $gBackOfficeMail;
        $this->_config = $gBackOfficeMail;

        parent::__construct();
    }
}
