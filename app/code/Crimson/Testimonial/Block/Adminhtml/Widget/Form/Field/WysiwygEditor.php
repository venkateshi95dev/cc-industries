<?php

namespace Crimson\Testimonial\Block\Adminhtml\Widget\Form\Field;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Escaper;

class WysiwygEditor extends Template implements RendererInterface
{

    /**
     * @var \Magento\Framework\Data\Form\Element\CollectionFactory
     */
    protected $_factoryCollection;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_factoryElement;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Backend\Helper\Data $backendData
    ) {
        $this->_factoryElement    = $factoryElement;
        $this->_factoryCollection = $factoryCollection;
        $this->_backendData       = $backendData;
        $this->_wysiwygConfig     = $wysiwygConfig;
        parent::__construct($context);
    }


    public function render(AbstractElement $element)
    {
        $html  = '';
        $value = $element->getValue();
        $class = '';
        if($element->getRequired()) {
            $class = 'required-entry';
        }

        $html .= '<div class="admin__field field field-options_'.$element->getId().'  with-note">';
        $html .= $element->getLabelHtml();
        $html .= '<div class="admin__field-control control">';
        $html .= '<textarea id="'.$element->getHtmlId().'" name="'.$element->getName().'" class="textarea admin__control-textarea wysiwyg-editor '.$class.'" rows="5" cols="15" data-ui-id="product-tabs-attributes-tab-fieldset-element-textarea-'.$element->getName().'" aria-hidden="true">'.$value.'</textarea>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;

    }
}
