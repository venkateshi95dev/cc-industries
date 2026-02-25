<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_OrderEdit
 */

namespace I95DevConnect\OrderEdit\Helper;

use Exception;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data as MQHelper;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use Magento\Directory\Model\Region;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Order edit base helper
 */
class Data extends AbstractHelper
{

    /**
     * Enabled config path
     */
    public const XML_PATH_ENABLED = 'i95devconnect_orderEdit/orderedit_enabled_settings/enable_orderedit';
    public const STREET = 'street';
    public const STREET_2 = 'street2';
    public const FIRST_NAME = 'firstName';
    public const LAST_NAME = 'lastName';
    public const REGION_ID = 'regionId';
    public const POSTCODE = 'postcode';
    public const COUNTRYID = 'countryId';
    public const TELEPHONE = 'telephone';
    public const REGIONNAME = 'region_name';
    public const REGIONID = 'region_id';
    public const COMPANY = 'company';
    public const PREFIX = 'prefix';
    public const SUFFIX = 'suffix';

    /**
     *
     * @var type \I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory
     */
    public $logger;

    /**
     *
     * @var $scopeConfig
     */
    public $scopeConfig;

    /**
     * @var Region
     */
    public $regionFactory;

    /**
     * @var MQHelper
     */
    public $mqHelper;

    /**
     *
     * @var SalesOrderFactory
     */
    public $customSalesOrder;

    /**
     * @var OrderInterfaceFactory
     */
    public $orderFactory;
    /**
     *
     * @param Context $context
     * @param LoggerInterfaceFactory $logger
     * @param Region $regionFactory
     * @param OrderInterfaceFactory $orderFactory
     * @param MQHelper $mqHelper
     * @param SalesOrderFactory $customSalesOrder
     */
    public function __construct(
        Context $context,
        LoggerInterfaceFactory $logger,
        Region $regionFactory,
        OrderInterfaceFactory $orderFactory,
        MQHelper $mqHelper,
        SalesOrderFactory $customSalesOrder
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->logger = $logger;
        $this->regionFactory = $regionFactory;
        $this->orderFactory = $orderFactory;
        $this->mqHelper = $mqHelper;
        $this->customSalesOrder = $customSalesOrder;
        parent::__construct($context);
    }

    /**
     * Check if module is enabled
     *
     * @return string|null
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Compare between order address and edited address, its same or not
     *
     * @param object $orderAddress
     * @param array $editaddress
     * @return boolean
     */
    public function compareAddress($orderAddress, $editaddress)
    {
        try {
            $street1 = (isset($editaddress[self::STREET]) ? $editaddress[self::STREET] : "");
            $street2 = (isset($editaddress[self::STREET_2]) ? $editaddress[self::STREET_2] : "");
            $existingStreet1 = isset($orderAddress->getStreet()[0]) ? $orderAddress->getStreet()[0] : "";
            $existingStreet2 = isset($orderAddress->getStreet()[1]) ? $orderAddress->getStreet()[1] : "";
            $firstName = (isset($editaddress[self::FIRST_NAME]) ? $editaddress[self::FIRST_NAME] : "");
            $lastName = (isset($editaddress[self::LAST_NAME]) ? $editaddress[self::LAST_NAME] : "");
            $city = (isset($editaddress['city']) ? $editaddress['city'] : "");
            $streetDetails = $this->getStreetDetails($editaddress);
            $postCode = $streetDetails["postCode"] ?? '';
            $countryId = $streetDetails["countryId"] ?? '';
            $telePhone = $streetDetails["telePhone"] ?? '';
            $regionCode = $streetDetails["regionCode"] ?? '';
            if (trim((string)$orderAddress->getPostcode()) == trim((string)$postCode)
                && trim((string)$existingStreet1) == trim((string)$street1)
                && trim((string)$existingStreet2) == trim((string)$street2)
                && trim((string)$orderAddress->getCity()) == trim((string)$city)
                && trim((string)$orderAddress->getRegion()) == trim((string)$regionCode[self::REGIONNAME])
                && trim((string)$orderAddress->getCountryId()) == trim((string)$countryId)
                && trim((string)$orderAddress->getTelephone()) == trim((string)$telePhone)
                && trim((string)$orderAddress->getFirstname()) == trim((string)$firstName)
                && trim((string)$orderAddress->getLastname()) == trim((string)$lastName)
            ) {
                $compare = true;
            } else {
                $compare = false;
            }
        } catch (Exception $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
        }
        return $compare;
    }

    /**
     * Get street details
     *
     * @param array $address
     * @return array
     */
    public function getStreetDetails($address)
    {
        return [
            "street" => (isset($address[self::STREET]) ? $address[self::STREET] : ""),
            "street2" => (isset($address[self::STREET_2]) ? $address[self::STREET_2] : ""),
            "region" => (isset($address[self::REGION_ID]) ? $address[self::REGION_ID] : ""),
            "countryId" => (isset($address[self::COUNTRYID]) ? $address[self::COUNTRYID] : ""),
            "regionCode" => $this->getRegionInfo(
                (isset($address[self::REGION_ID]) ? $address[self::REGION_ID] : ""),
                (isset($address[self::COUNTRYID]) ? $address[self::COUNTRYID] : "")
            )
        ];
    }

    /**
     * Get regionId and regionName from region and country id
     *
     * @param int $region
     * @param int $countryId
     * @return array
     */
    public function getRegionInfo($region, $countryId)
    {
        $regionModel = $this->regionFactory->loadByCode($region, $countryId);
        $regionCode = $regionModel->getName();
        if (!isset($regionCode)) {
            $regionCode = $region;
        }
        $regionId = $regionModel->getId();
        return [self::REGIONID => $regionId, self::REGIONNAME => $regionCode];
    }

    /**
     * Find address info for order data
     *
     * @param array $address
     * @Exception critical
     * @return array
     */
    public function prepareAddress($address)
    {
        try {
            $street = (isset($address[self::STREET]) ? $address[self::STREET] : "");
            $street2 = (isset($address[self::STREET_2]) ? $address[self::STREET_2] : "");
            $streetDetails = $this->getStreetDetails($address);
            $countryId = $streetDetails["countryId"] ?? '';
            $regionCode = $streetDetails["regionCode"] ?? '';
            $addressData = [
                'firstname' => (isset($address[self::FIRST_NAME]) ? $address[self::FIRST_NAME] : ""),
                'middlename' => (isset($address['middleName']) ? $address['middleName'] : ""),
                'lastname' => (isset($address[self::LAST_NAME]) ? $address[self::LAST_NAME] : ""),
                self::STREET => [$street, $street2],
                'city' => (isset($address['city']) ? $address['city'] : ""),
                'region' => $regionCode[self::REGIONNAME],
                self::COMPANY => (isset($address[self::COMPANY]) ? $address[self::COMPANY] : ""),
                self::REGIONID => $regionCode[self::REGIONID],
                self::POSTCODE => (isset($address[self::POSTCODE]) ? $address[self::POSTCODE] : ""),
                'country_id' => $countryId,
                self::TELEPHONE => (isset($address[self::TELEPHONE]) ? $address[self::TELEPHONE] : ""),
                'fax' => (isset($address['fax']) ? $address['fax'] : ""),
                self::PREFIX => (isset($address[self::PREFIX]) ? $address[self::PREFIX] : ""),
                self::SUFFIX => (isset($address[self::SUFFIX]) ? $address[self::SUFFIX] : "")
            ];
        } catch (Exception $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
        }
        return $addressData;
    }

    /**
     * Get order status
     *
     * @param string[] $stringData
     * @return string
     */
    public function getOrderStatus($stringData)
    {
        try {
            $magentoItems = [];
            $orderItems = $this->mqHelper->getValueFromArray("orderItems", $stringData);
            $order = $this->getOrder($stringData);
            if ($order && !$order->getId()) {
                throw new LocalizedException(__('edit_order_007'));
            }
            foreach ($order->getAllItems() as $item) {
                $magentoSku = strtolower($item->getSku());
                $magentoItems[$magentoSku]["sku"] = $magentoSku;
                $magentoItems[$magentoSku]["qty"] = $item->getQtyOrdered();
                $magentoItems[$magentoSku]["price"] = $item->getPrice();
                $magentoItems[$magentoSku]["markdownPrice"] = $item->getDiscountAmount();
            }

            $shippingAmount = $stringData['shippingAmount'] ?? 0;
            $magentoShippingAmount = $order->getShippingAmount();
            if (count($magentoItems) != count($orderItems)
                || $magentoShippingAmount != $shippingAmount
                //|| $magentoOrderTotal != $orderTotal
            ) {
                return "edited";
            }
            return $this->getItemStatus($magentoItems, $orderItems);
        } catch (LocalizedException $ex) {
            $message = $ex->getMessage();
            $this->logger->create()->createLog(
                __METHOD__,
                $message,
                LoggerInterface::I95EXC,
                'critical'
            );
            throw new LocalizedException(__($message));
        }
    }

    /**
     * Check for given order exist. If exists return that order else throw error.
     *
     * @param string[] $dataString
     * @return object
     */
    public function getOrder($dataString)
    {
        $loadCustomOrder = $this->customSalesOrder->create()
            ->getCollection()
            ->addFieldToFilter("target_order_id", $dataString['targetId'])
            ->setOrder('id', 'DESC')
            ->getFirstItem();
        $sourceOrderId = $loadCustomOrder->getSourceOrderId();

        if ($sourceOrderId === '') {
            throw new LocalizedException(__('edit_order_004'));
        } else {
            return $this->getOrderByIncrementId($sourceOrderId);
        }
    }

    /**
     * Get order by increment id
     *
     * @param int $incrementId
     * @return mixed
     * @throws LocalizedException
     */
    public function getOrderByIncrementId($incrementId)
    {
        try {
            return $this->orderFactory->create()->loadByIncrementId($incrementId);
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Get item status
     *
     * @param array $magentoItems
     * @param array $orderItems
     * @return string
     */
    public function getItemStatus($magentoItems, $orderItems)
    {
        $status = "updated";
        $itemCheck = ['price', 'qty', 'markdownPrice'];
        foreach ($orderItems as $orderItem) {
            $itemSku = strtolower($orderItem["sku"]);
            foreach ($itemCheck as $checkField) {
                if (isset($magentoItems[$itemSku])
                    && $magentoItems[$itemSku][$checkField] != $orderItem[$checkField]) {
                    return "edited";
                } elseif (isset($magentoItems[$itemSku])
                    && $magentoItems[$itemSku][$checkField] == $orderItem[$checkField]) {
                    $status = "updated";
                } else {
                    return "edited";
                }
            }
        }
        return $status;
    }
}