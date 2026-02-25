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

use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\PaymentHistory;
use Ebizcharge\Ebizcharge\Model\Recurring;
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * This class is used to add different classes to status as per status value
 *
 * Class CustomerIdRenderer
 */
class PaymentRecurringReferer extends Column
{
    /**
     * @var Config
     */
    protected Config $_configModel;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManagerInterface;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $_urlInterface;

    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $_recurringFactory;

    /**
     * PaymentRecurringReferer constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Config $configModel
     * @param StoreManagerInterface $storeManagerInterface
     * @param UrlInterface $urlInterface
     * @param RecurringFactory $recurringFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Config $configModel,
        StoreManagerInterface $storeManagerInterface,
        UrlInterface $urlInterface,
        RecurringFactory $recurringFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var _configModel */
        $this->_configModel = $configModel;
        /** @var  _urlInterface */
        $this->_urlInterface = $urlInterface;
        /** @var  _storeManagerInterface */
        $this->_storeManagerInterface = $storeManagerInterface;
        /** @var  _recurringFactory */
        $this->_recurringFactory = $recurringFactory;
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
            $paymentRefNumber = PaymentHistory::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_REFERENCE_NUMBER;

            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$paymentRefNumber])) {
                    $item[$paymentRefNumber] = $this->getRecurringRefererHtml($item);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get Recurring Refferer HTML
     *
     * @param array $paymentHistoryItem
     * @return string
     */
    protected function getRecurringRefererHtml($paymentHistoryItem = []): string
    {
        /** @var  $paymentRefNumber */
        $paymentRefNumber = $paymentHistoryItem[PaymentHistory::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_REFERENCE_NUMBER];
        /** @var  $scheduledPaymentInternalId */
        $scheduledPaymentInternalId = $paymentHistoryItem[
            PaymentHistory::EBIZCHARGE_PAYMENT_HISTORY_RECURRING_SCHEDULED_PAYMENT_INTERNAL_ID
        ];

        $recurring = $this->_recurringFactory->create()->loadByScheduledPaymentInternalId($scheduledPaymentInternalId);

        /** @var  $recurringUrl */
        $recurringUrl = '';

        /**
         * Recurring if get Id
         */
        if ($recurring && $recurring->getId()) {
            $recurringId = $recurring->getData(Recurring::ENTITY_ID);
            $mageCustomerId = $recurring->getData(Recurring::MAGE_CUST_ID);
            $paymentMethodId =  $recurring->getData(Recurring::EB_REC_METHOD_ID);
            $recurringUrl = $this->_urlInterface->getUrl(
                'ebizcharge_ebizcharge/recurrings/editaction/magcid/' . $mageCustomerId . '/rid/' .
                $recurringId . '/mid/' . $paymentMethodId
            );
        }

        if ($recurringUrl !== '') {
            $html = '<a target="_blank" title="' .
                __('Please click to open the recurring against this transaction ' . $recurringUrl) .
                '" href="' . $recurringUrl . '">';
            $html .= '<span ><span> ' . $paymentRefNumber . '</span></span>';
            $html .= '</a>';
        } else {
            $html = $paymentRefNumber;
        }

        return $html;
    }
}
