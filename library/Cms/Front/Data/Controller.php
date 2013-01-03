<?php

abstract class Core_Cms_Front_Data_Controller
{
    /**
     * @var App_Cms_Front_Data
     */
    protected $_data;

    /**
     * @var App_Cms_Front_Document
     */
    protected $_document;

    /**
     * @var App_Cms_Front_Document
     */
    protected $_parentDocument;

    protected $_content;
    protected $_type;

    /**
     * @param App_Cms_Front_Data $_data
     * @param App_Cms_Front_Document $_document
     */
    public function __construct($_data, $_document)
    {
        $this->_data = $_data;
        $this->_document = $_document;

        $this->_parentDocument = $this->_document->id != $this->_data->frontDocumentId
                               ? App_Cms_Front_Document::getById($this->_data->frontDocumentId)
                               : $this->_document;

        $proceedResult = $this->_data->proceedContent($this->_parentDocument);

        $this->setType(
            $proceedResult && !empty($proceedResult['type'])
          ? $proceedResult['type']
          : $this->_data->getTypeId()
        );

        $this->setContent(
            $proceedResult && key_exists('content', $proceedResult)
          ? $proceedResult['content']
          : $this->_data->content
        );
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
        $method = $this->_data->getTypeId() == 'xml' ? 'node' : 'cdata';
        return Ext_Xml::$method($this->_data->tag, $this->getContent());
    }
}
