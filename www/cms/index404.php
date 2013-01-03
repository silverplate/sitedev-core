<?php

require_once 'prepend.php';

$page = new App_Cms_Back_Page_404();

$page->setTitle('Страница не найдена');
$page->addContent(Ext_Xml::cdata(
    'html',
    '<p class="text">
    Страница <i>' . $page->getUrl('path') . '</i> не&nbsp;найдена.
    Если&nbsp;вы&nbsp;уверены, что&nbsp;произошла ошибка, пожалуйста, сообщите о&nbsp;ней
    по&nbsp;адресу <a href="mailto:support@sitedev.ru">support@sitedev.ru</a>.
    </p>'
));

$page->output();
