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

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Recurring grid actions column
 *
 * Class PaymentHistoryActions
 */
class PaymentHistoryActions extends Column
{
    /**
     * URL Path
     *
     * @const URL_PATH_EMAIL_RECEIPT_ACTION
     */
    public const URL_PATH_EMAIL_RECEIPT_ACTION = 'ebizcharge_ebizcharge/recurrings/emailAction';

    /**
     * URL Path
     *
     * @const URL_PATH_PRINT_RECEIPT_ACTION
     */
    public const URL_PATH_PRINT_RECEIPT_ACTION = 'ebizcharge_ebizcharge/recurrings/printAction';

    /**
     * @const URL_PATH_EXPORT
     */
    public const URL_PATH_EXPORT = 'ebizcharge_ebizcharge/recurrings/datesexportaction';

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * Main class Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var  urlBuilder */
        $this->urlBuilder = $urlBuilder;
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
                $fieldName = $this->getData('name');

                // payment_detail_user = payment_ref_number
                $sendEmailUrl = $this->urlBuilder->getUrl(
                    self::URL_PATH_EMAIL_RECEIPT_ACTION,
                    [
                        'customer_id' => $item['customer_id'],
                        'email' => strip_tags($item['customer_email']),
                        'entity_id' => $item['entity_id'],
                        'tid' => $item['payment_detail_user'],
                        'rid' => $item['payment_detail_user']
                    ]
                );
                $printEmailUrl = $this->urlBuilder->getUrl(
                    self::URL_PATH_PRINT_RECEIPT_ACTION,
                    [
                        'customer_id' => $item['customer_id'],
                        'email' => strip_tags($item['customer_email']),
                        'entity_id' => $item['entity_id'],
                        'tid' => $item['payment_detail_user'],
                        'rid' => $item['payment_detail_user']
                    ]
                );

                $item[$fieldName . '_html'] = "<select id='" . $fieldName . "-" . $item['entity_id'] .
                    "'  class='action-menu-item print-actions admin__control-select'
                    style='width:120px;font-size:11px;' name='" . $fieldName . "-" . $item['entity_id'] .
                    "'  >
                        <option data-type='' value=''>" . __('Please Select') . "</option>
                        <option data-type='send-email' value='" . $sendEmailUrl . "'>" .
                    __('Email Receipt') . "</option>
                        <option data-type='print-email' value='" . $printEmailUrl . "'>" .
                    __('Print Receipt') . "</option>
                    </select>
                    ";

                $item[$fieldName . '_title'] = __('Action alert Box ');
                $item[$fieldName . '_rowindex'] = $item['entity_id'];
            }
        }
        return $dataSource;
    }
}
