<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\Recurring;

use Ebizcharge\Ebizcharge\Model\ProductFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Recurring Product Column Ui Column Component
 *
 * Class ProductColumn
 */
class ProductColumn extends Column
{
    /**
     * @var Escaper
     */
    protected Escaper $_escaper;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $_urlInterface;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;

    /**
     * ProductColumn constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param ProductFactory $productFactory
     * @param UrlInterface $urlInterface
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        ProductFactory $productFactory,
        UrlInterface $urlInterface,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var  escaper */
        $this->_escaper = $escaper;
        /** @var  _urlInterface */
        $this->_urlInterface = $urlInterface;
        /** @var  _productFactory */
        $this->_productFactory = $productFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $columnName = nl2br($this->_escaper->escapeHtml($item[$this->getData('name')]));
                $productSku = $item['product_sku'];
                $item[$this->getData('name')] = $this->getLabelInfo($columnName, $productSku);
            }
        }

        return $dataSource;
    }

    /**
     * Get html as per getLabelInfo
     *
     * @param mixed $gridLabel
     * @param null|mixed $productSku
     * @return string
     */
    private function getLabelInfo($gridLabel, $productSku = null): string
    {
        $gridLabel = $gridLabel ? $gridLabel : __('*');
        $cssClass = '';
        try {
            $productFactory = $this->_productFactory->create();
            $productId = $productFactory->getIdBySku($productSku);

            $url = $this->_urlInterface->getUrl('catalog/product/edit', ['id' => $productId]);

            return '<a href="' . $url . '" target="_parent" title="' .
                __("Please click to go to Product.") . '" > <span class="' . $cssClass .
                '"><span>' . $gridLabel . '</span></span></a>';
        } catch (LocalizedException $localizedException) {
            return '<span class="' . $cssClass . '"><span>' . $gridLabel . '</span></span>';
        }
    }
}
