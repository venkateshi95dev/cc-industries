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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\Product;

use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\ProductFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Division Id Renderer Ui Component
 *
 * Class DivisionIdRenderer
 */
class DivisionIdRenderer extends Column
{
    /**
     * @var Config
     */
    protected Config $_ebizchargeConfigModel;

    /**
     * @var Http
     */
    protected Http $_httpRequest;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Config $ebizchargeConfigModel
     * @param ProductFactory $productFactory
     * @param Http $httpRequest
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Config $ebizchargeConfigModel,
        ProductFactory $productFactory,
        Http $httpRequest,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        /** @var _ebizchargeConfigModel */
        $this->_ebizchargeConfigModel = $ebizchargeConfigModel;
        /** @var _httpRequest */
        $this->_httpRequest = $httpRequest;
        /** @var  _productFactory */
        $this->_productFactory = $productFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $divisionId = $item[$this->getData('name')];
                    $item[$this->getData('name')] = $item[$this->getData('name')];
                }
            }
        }

        return $dataSource;
    }
}
