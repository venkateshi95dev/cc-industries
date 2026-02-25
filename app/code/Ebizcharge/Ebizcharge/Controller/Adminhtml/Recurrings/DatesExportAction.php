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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Exception;
use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\AbstractRepository;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\Grid\CollectionFactory as RecurringCollection;
use Magento\Backend\App\Action;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\File\Csv;


/**
 * Export dates
 * Deletes the customer's saved payment method
 *
 * Class DatesExportAction
 */
class DatesExportAction extends Action implements HttpGetActionInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var Csv
     */
    private Csv $csvProcessor;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var FileFactory
     */
    private FileFactory $fileFactory;

    /**
     * @var RecurringCollection
     */
    private RecurringCollection $recurringCollectionFactory;

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;

    protected $fileName = 'Subscriptions.xls';


    protected CustomerFactory $customerFactory;

    /**
     * @param Csv $csvProcessor
     * @param FileFactory $fileFactory
     * @param DirectoryList $directoryList
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RecurringCollection $recurringCollectionFactory
     * @param CustomerFactory $customerFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param Context $context
     */
    public function __construct(
        Csv                   $csvProcessor,
        FileFactory           $fileFactory,
        DirectoryList         $directoryList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RecurringCollection   $recurringCollectionFactory,
        CustomerFactory       $customerFactory,
        EbizchargeLogger      $ebizchargeLogger,
        Context               $context
    )
    {
        parent::__construct($context);

        /** @var  searchCriteriaBuilder */
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        /** @var  csvProcessor */
        $this->csvProcessor = $csvProcessor;
        /** @var  directoryList */
        $this->directoryList = $directoryList;
        /** @var  recurringCollectionFactory */
        $this->recurringCollectionFactory = $recurringCollectionFactory;
        /** @var  fileFactory */
        $this->fileFactory = $fileFactory;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var  customerFactory */
        $this->customerFactory = $customerFactory;
    }

    /**
     * Deletes customer's payment method.
     *
     * @return ResultInterface|ResponseInterface
     * @phpcs:disable
     */
    public function execute()
    {
        $recurringId = $this->getRequest()->getParam('rid');
        $filePath = $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $this->fileName;
        $csvRows[] = $this->prepareCSVFileHeaders();

        if (!empty($recurringId)) {
            try {
                if ($subscriptionRecords = $this->getRecurringRecords()) {
                    $subscriptionRows = $this->prepareCSVRows($subscriptionRecords);
                    $subscriptionRows = array_merge($csvRows, $subscriptionRows);
                    $this->csvProcessor->setEnclosure('"')->setDelimiter(',')->saveData($filePath, $subscriptionRows);
                }
            } catch (Exception $ex) {
                $this->ebizchargeLogger->addCritical(__("Exception occurred during exporting the subscriptions " . $ex->getMessage()));
            }
        }
        return $this->fileFactory->create(
            $this->fileName,
            [
                'type' => "filename",
                'value' => $this->fileName,
                'rm' => true,
            ],
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA,
            'application/octet-stream'
        );
    }
    // phpcs:enable

    /**
     * Get records to export
     *
     * @return bool|mixed
     */
    private function getRecurringRecords()
    {

        $recurringItems = [];
        try {
            $recurringId = $this->getRequest()->getParam('rid');
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                RecurringInterface::ENTITY_ID,
                $recurringId
            );
            $records = AbstractRepository::searchList(
                $searchCriteria->create(),
                $this->recurringCollectionFactory->create()
            );
            $recurringItems = !empty($records->getItems()) ? $records->getItems() : [];

        } catch (Exception $ex) {
            $this->ebizchargeLogger->addCritical(__("Exception occurred getting records from subscriptions " . $ex->getMessage()));

        }
        return $recurringItems;
    }

    /**
     * @return array
     */
    protected function prepareCSVFileHeaders()
    {

        $csvHeaders = [
            "Entity ID",
            "Recurring ID",
            "Recurring Status",
            "Is Indefinitely",
            "Customer ID",
            "Customer Email",
            "customer_name",
            "Order ID",
            "Product ID",
            "product_sku",
            "Product Name",
            "Qty Ordered",
            "Store ID",
            "Coupon",
            "Cart Rule ID",
            "Discount",
            "Start Date",
            "End Date",
            "Frequency",
            "Payment Method ID",
            "Payment Method",
            "Recurring Internal ID",
            "Total Amount",
            "Total Recurring",
            "Recurring Processed",
            "Next Recurring",
            "Remaining Recurring",
            "Recurring Due Dates",
            "Billing Address",
            "Shipping Address",
            "Shipping Method",
            "Failed Attempts",
            "created_at",
            "updated_at"

        ];
        return $csvHeaders;

    }

    /**
     * @param array $recurringRecords
     * @return array
     */
    protected function prepareCSVRows(array $recurringRecords = [])
    {
        $recurringRows = [];

        if (is_array($recurringRecords) && count($recurringRecords) > 0) {
            foreach ($recurringRecords as $key => $recurringRecord) {
                $recurringRecord = (array)$recurringRecord->getData();
                $billingAddress = "";
                $shippingAddress = "";
                $billingAddressId = $recurringRecord["billing_address_id"] ?? "";
                $shippingAddressId = $recurringRecord["shipping_address_id"] ?? "";

                if ($billingAddressId) {
                    $customerAddress = $this->customerFactory->create()->loadCustomerAddress($billingAddressId);
                    if ($customerAddress) {
                        $billingAddress = $customerAddress->getStreet()[0] ?? "";
                    }
                }
                if ($shippingAddressId) {
                    $customerAddress = $this->customerFactory->create()->loadCustomerAddress($shippingAddressId);
                    if ($customerAddress) {
                        $shippingAddress = $customerAddress->getStreet()[0] ?? "";
                    }
                }


                $recurringRows[$key] = [
                    $recurringRecord["entity_id"] ?? "",
                    $recurringRecord["rec_id"] ?? "",
                    $recurringRecord["rec_status"] ?? "",
                    $recurringRecord["rec_indefinitely"] ?? "",
                    $recurringRecord["mage_cust_id"] ?? "",
                    $recurringRecord["customer_email"] ?? "",
                    $recurringRecord["customer_name"] ?? "",
                    $recurringRecord["mage_order_id"] ?? "",
                    $recurringRecord["mage_item_id"] ?? "",
                    $recurringRecord["mage_item_name"] ?? "",
                    $recurringRecord["product_sku"] ?? "",
                    $recurringRecord["qty_ordered"] ?? "",
                    $recurringRecord["store_id"] ?? "",
                    $recurringRecord["coupon_code"] ?? "",
                    $recurringRecord["cart_rule_id"] ?? "",
                    $recurringRecord["discount"] ?? "",
                    $recurringRecord["eb_rec_start_date"] ?? "",
                    $recurringRecord["eb_rec_end_date"] ?? "",
                    $recurringRecord["eb_rec_frequency"] ?? "",
                    $recurringRecord["eb_rec_method_id"] ?? "",
                    $recurringRecord["eb_rec_scheduled_payment_internal_id"] ?? "",
                    $recurringRecord["eb_rec_total"] ?? "",
                    $recurringRecord["eb_rec_processed"] ?? "",
                    $recurringRecord["eb_rec_next"] ?? "",
                    $recurringRecord["eb_rec_remaining"] ?? "",
                    $recurringRecord["eb_rec_due_dates"] ?? "",
                    $billingAddress,
                    $shippingAddress,
                    $recurringRecord["amount"] ?? "",
                    $recurringRecord["payment_method_name"] ?? "",
                    $recurringRecord["shipping_method"] ?? "",
                    $recurringRecord["failed_attempts"] ?? "",
                    $recurringRecord["order_date"] ?? "",
                    $recurringRecord["created_at"] ?? "",
                    $recurringRecord["updated_at"] ?? ""
                ];
            }
        }
        return $recurringRows;
    }
}
