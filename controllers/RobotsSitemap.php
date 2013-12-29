<?php

namespace Core\Controller;

abstract class RobotsSitemapController extends CommonController
{
    public function output($_createCache = true)
    {
        header('Content-type: text/xml; charset=utf-8');
        echo $this->getHtml($_createCache);
    }

    public function execute()
    {
        parent::execute();

        $this->setTemplate(TEMPLATES . 'robots-sitemap.xsl');
        $type = App_Cms_Front_Navigation::load('robots-sitemap', 'name');

        if ($type && $type->isPublished) {
            $documents = App_Cms_Front_Navigation::getDocuments($type->name);
            $controllers = array();
            $sitemap = array();
            $sitemapXml = '';

            foreach ($documents as $doc) {
                $sitemap[] = self::_getSitemapItemFromDocument($doc);

                if ($doc->frontControllerId) {
                    if (!isset($controllers[$doc->frontControllerId])) {
                        $controllers[$doc->frontControllerId] = $doc->getController();
                    }

                    $controller = $controllers[$doc->frontControllerId];
                    if ($controller) {
                        $class = $controller->getClassName();
                        require_once $controller->getFilename();

                        if (method_exists($class, 'getRobotsSitemapItems')) {
                            $list = $class->getRobotsSitemapItems();

                            foreach ($list as $item) {
                                $sitemap[] = self::_getSitemapItem($item['uri'], '0.8');
                            }
                        }
                    }
                }
            }

            foreach ($sitemap as $item) {
                $xml = '';

                foreach ($item as $name => $value) {
                    $xml .= \Ext\Xml::cdata($name, $value);
                }

                \Ext\Xml::append(
                    $sitemapXml,
                    \Ext\Xml::notEmptyNode('url', $xml)
                );
            }

            $this->addContent($sitemapXml);
        }
    }

    protected static function _getSitemapItem($_uri,
                                              $_priority = 1,
                                              $_freq = 'always',
                                              $_date = null)
    {
        global $gHost;
        $date = empty($_date) ? date('c') : date('c', $_date);

        return array('loc' => "http://$gHost$_uri",
                     'lastmod' => $date,
                     'changefreq' => $_freq,
                     'priority' => $_priority);
    }

    protected static function _getSitemapItemFromDocument($_document)
    {
        return self::_getSitemapItem($_document->getUri());
    }
}
