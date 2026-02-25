<?php
/**
 * @namespace   Crimson
 * @module      Shipping
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        03/19/2019
 */
namespace Crimson\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

/**
 * Class ExportTwoday
 * @package Crimson\Shipping\Block\Adminhtml\System\Config\Form\Field
 */
class ExportTwoday extends AbstractElement
{
    const TABLE_TWODAY_EXPORT = 'carriers_tablerate_twoday_export';

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
        if (!empty($htmlId) && $htmlId == self::TABLE_TWODAY_EXPORT) {
            /** @var Button $buttonBlock  */
            $buttonBlock = $this->getForm()->getParent()->getLayout()->createBlock(Button::class);
            $params = ['website' => $buttonBlock->getRequest()->getParam('website')];
            $url = $this->_backendUrl->getUrl("*/*/exportTwodayTablerates", $params);
            $data = [
                'label' => __('Export Two Day Rates CSV'),
                'onclick' => "setLocation('" .
                    $url .
                    "conditionName/' + $('carriers_tablerate_twoday_condition_name').value + '/tablerates.csv' )",
                'class' => '',
            ];

            return $buttonBlock->setData($data)->toHtml();
        } else {
            return parent::getElementHtml();
        }
    }
}
