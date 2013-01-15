<?php

abstract class Core_Cms_Back_Mail extends Core_Cms_Mail
{
    public function __construct()
    {
        global $gBackOfficeMail;
        $this->_config = $gBackOfficeMail;

        parent::__construct();
    }
}
