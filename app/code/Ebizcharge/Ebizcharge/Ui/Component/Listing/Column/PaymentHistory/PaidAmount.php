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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\PaymentHistory;

use Ebizcharge\Ebizcharge\Model\ProductFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * This class is used to add different classes to status as per status value
 *
 * Class ResultCardInfo
 */
class PaidAmount extends Column
{
    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;

    /**
     * PaidAmount constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ProductFactory $productFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProductFactory $productFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var _productFactory */
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
            $paidAmount = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$paidAmount])) {
                    $item[$this->getData('name')] = $this->getPriceHtml($item[$paidAmount]);
                }
            }
        }
        return $dataSource;
    }

    /**
     * Get status html as per $paidAmount
     *
     * @param mixed $amount
     * @return string
     */
    private function getPriceHtml($amount): string
    {
        /** @var $paidAmount */
        $paidAmount = $this->_productFactory->create()->getCurrencyWithFormat($amount);
        $class = '';
        return '<span class="' . $class . ' "><span>' . $paidAmount . '</span></span>';
    }
}
