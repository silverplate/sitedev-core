<?php

require_once 'prepend.php';

global $gSiteLang, $gIsHidden, $gCache;

$gCache = empty($gSiteLang)
        ? new \App\Cms\Cache\Project()
        : new \App\Cms\Cache\Project(null, $gSiteLang);

if ($gCache->isAvailable() && $gCache->isCache()) {
    echo $gCache;

} else {
    $realUrl = \Ext\File::parseUrl();
    $uri = rtrim($realUrl['path'], '/') . '/';

    if (!empty($realUrl['query'])) {
        $uri .= '?' . $realUrl['query'];
    }

    $url = \Ext\File::parseUrl($uri);
    $document = \App\Cms\Front\Document::load($url['path'], 'uri');

    if (!($document && ($document->isPublished || !empty($gIsHidden)))) {
        $url = \Ext\File::parseUrl(getCustomUrl($uri));
        $document = \App\Cms\Front\Document::load($url['path'], 'uri');
    }

    if (
        $document &&
        $document->link &&
        $document->link != $realUrl['path']
    ) {
        goToUrl($document->link);

    } else if (
        $document &&
        $document->getController() &&
        ($document->isPublished == 1 || !empty($gIsHidden)) &&
        (!$document->authStatusId || is_null(\App\Cms\User::getAuthGroup()) || $document->authStatusId & \App\Cms\User::getAuthGroup())
    ) {
        $controller = \App\Cms\Front\Document::initController(
            $document->getController(),
            $document
        );

        $controller->execute();
        $controller->output();

    } else {
        documentNotFound();
    }
}

function getCustomUrl($_url)
{
    global $gCustomUrls, $gSiteLang;

    $url = !empty($gSiteLang) && 0 === strpos($_url, "/$gSiteLang/")
         ? substr($_url, strlen($gSiteLang) + 1)
         : $_url;

    if (empty($gCustomUrls)) {
        return $url;

    } else {
        $urls = $gCustomUrls;
        array_walk($urls, 'escapeUrl');
        preg_match('/^\/(' . implode('|', $urls) . ')\//', $url, $matches);

        return (empty($gSiteLang) ? '' : "/$gSiteLang/") . ($matches ? $matches[0] : $url);
    }
}

function escapeUrl(&$_item)
{
    $_item = preg_replace('/(\/|\-)/', '\\\$1', $_item);
}
