<?php

namespace Core\Cms\Front\Data;

abstract class Controller
{
    /**
     * @var \App\Cms\Front\Data
     */
    protected $_data;

    /**
     * @var \App\Cms\Front\Document
     */
    protected $_document;

    /**
     * @var \App\Cms\Front\Document
     */
    protected $_parentDocument;

    protected $_content;
    protected $_type;

    /**
     * @param \App\Cms\Front\Data $_data
     * @param \App\Cms\Front\Document $_document
     */
    public function __construct($_data, $_document)
    {
        $this->_data = $_data;
        $this->_document = $_document;

        $this->_parentDocument = $this->_document->id != $this->_data->frontDocumentId
                               ? \App\Cms\Front\Document::getById($this->_data->frontDocumentId)
                               : $this->_document;

        $proceedResult = $this->_data->proceedContent($this->_parentDocument);

        $this->setType(
            $proceedResult && !empty($proceedResult['type'])
          ? $proceedResult['type']
          : $this->_data->getTypeId()
        );

        $this->setContent(
            $proceedResult && array_key_exists('content', $proceedResult)
          ? $proceedResult['content']
          : $this->_data->content
        );

        if (method_exists($this, 'execute')) {
            $this->execute();
        }
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function setContent($_value)
    {
        $this->_content = $_value;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setType($_type)
    {
        return $this->_type = $_type;
    }

    public function getXml()
    {
        $method = $this->getType() == 'xml' ? 'node' : 'cdata';
        return \Ext\Xml::$method($this->_data->tag, $this->getContent());
    }
}
