<?php

require_once 'prepend.php';

$gCache = '' == SITE_LANG
        ? new App_Cms_Cache_Project()
        : new App_Cms_Cache_Project(null, SITE_LANG);

if ($gCache->isAvailable() && $gCache->isCache()) {
    echo $gCache;

} else {
    $realUrl = parse_url($_SERVER['REQUEST_URI']);
    $url = parse_url(getCustomUrl($_SERVER['REQUEST_URI']));
    $document = App_Cms_Front_Document::load($url['path'], 'uri');

    if (
        $document &&
        $document->link &&
        $document->link != $realUrl['path']
    ) {
        goToUrl($document->link);

    } else {
        if (
            $document &&
            $document->getController() &&
            ($document->isPublished == 1 || IS_HIDDEN) &&
            (!$document->authStatusId || is_null(App_Cms_User::getAuthGroup()) || $document->authStatusId & App_Cms_User::getAuthGroup())
        ) {
            $controller = App_Cms_Front_Document::initController($document->getController(), $document);
            $controller->execute();
            $controller->output();

        } else {
            documentNotFound();
        }
    }
}


function getCustomUrl($_url)
{
    global $gCustomUrls;


    $url = '' != SITE_LANG && 0 === strpos($_url, '/' . SITE_LANG . '/')
         ? substr($_url, strlen(SITE_LANG) + 1)
         : $_url;

    if (empty($gCustomUrls)) {
        return $url;

    } else {
        $urls = $gCustomUrls;
        array_walk($urls, 'escapeUrl');
        preg_match('/^\/(' . implode('|', $urls) . ')\//', $url, $matches);
        return (SITE_LANG ? '/' . SITE_LANG : '') . ($matches ? $matches[0] : $url);
    }
}

function escapeUrl(&$_item)
{
    $_item = preg_replace('/(\/|\-)/', '\\\$1', $_item);
}
