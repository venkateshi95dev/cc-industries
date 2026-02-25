<?php
/**
 * @namespace   Crimson
 * @module      Shipping
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/25/2019
 */
namespace Crimson\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

/**
 * Class ExportSurepost
 * @package Crimson\Shipping\Block\Adminhtml\System\Config\Form\Field
 */
class ExportSurepost extends AbstractElement
{
    const TABLE_SUREPOST_EXPORT = 'carriers_tablerate_surepost_export';

    /**
     * @var UrlInterface
     */
    protected $_backendUrl;

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        UrlInterface $backendUrl,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_backendUrl = $backendUrl;
    }

    /**
     * @return string
     */
    public function getElementHtml(): string
    {
        $htmlId = $this->getHtmlId();
        if (!empty($htmlId) && $htmlId == self::TABLE_SUREPOST_EXPORT) {
            /** @var Button $buttonBlock  */
            $buttonBlock = $this->getForm()->getParent()->getLayout()->createBlock(Button::class);
            $params = ['website' => $buttonBlock->getRequest()->getParam('website')];
            $url = $this->_backendUrl->getUrl("*/*/exportSurepostTablerates", $params);
            $data = [
                'label' => __('Export Surepost Rates CSV'),
                'onclick' => "setLocation('" .
                    $url .
                    "conditionName/' + $('carriers_tablerate_surepost_condition_name').value + '/tablerates.csv' )",
                'class' => '',
            ];

            return $buttonBlock->setData($data)->toHtml();
        } else {
            return parent::getElementHtml();
        }
    }
}
