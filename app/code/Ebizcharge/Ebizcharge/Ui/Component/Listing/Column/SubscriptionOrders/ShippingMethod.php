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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\SubscriptionOrders;

use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Payment method renderer
 *
 * Class PaymentMethod
 */
class ShippingMethod extends Column
{

    /**
     * @var ContextInterface
     */
    protected $context;
    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;
    /**
     * @var array
     */
    protected $components;
    /**
     * @var array
     */
    protected array $data;

    protected RecurringFactory $recurringFactory;


    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        RecurringFactory   $recurringFactory,
        array              $components = [],
        array              $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->context = $context;
        $this->uiComponentFactory = $uiComponentFactory;
        $this->components = $components;
        $this->data = $data;
        $this->recurringFactory = $recurringFactory;
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
            $paymentMethod = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $recurringId = $item["recurring_id"] ?? "";
                $item[$paymentMethod] = $this->getPaymentMethod($recurringId);

            }
        }
        return $dataSource;
    }

    /**
     * @param $recurringId
     * @return string
     */
    private function getPaymentMethod($recurringId = null): string
    {
        $recurringFactory = $this->recurringFactory->create();
        $recurring = $recurringFactory->load($recurringId);
        return trim($recurring->getShippingMethod());
    }
}
