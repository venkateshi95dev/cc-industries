<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_OrderEdit
 */

namespace I95DevConnect\OrderEdit\Model\DataPersistence\OrderEdit;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class for update order
 */
class Update extends AbstractOrderEdit
{
    public const CUSTOMER = 'customer';
    public const BILLING_ADDRESS = 'billingAddress';

    /**
     * @var bool
     */
    public $isSameBillingAddress =  true;
    /**
     * @var bool
     */
    public $isSameShippingAddress = true;
    /**
     * @var string
     */
    public $editBillingAddress = '';
    /**
     * @var string
     */
    public $editShippingAddress = '';
    /**
     * @var string
     */
    public $billingComment = '';
    /**
     * @var string
     */
    public $shippingComment = '';
    /**
     * @var string
     */
    public $orderUpdateErr = 'order_update_001';
    public $order;
    public $orderId;
    public $customerId;
    public $customerEmail;
    public $component;

    /**
     * Update order info(Shipping Address,Billing Address,Comments) send by ERP
     *
     * @param array $stringData
     * @return string
     * @throws LocalizedException
     */
    public function updateOrder($stringData)
    {
        $this->setStringData($stringData);
        try {
            $this->order = $this->editOrderValidator->validateOrderToUpdate($stringData);
            $this->orderId = $this->order->getId();
            $this->checkForCustomerData();
            $this->checkForAddressChanges();
            $comments = (isset($this->stringData['comments'])) ? $this->stringData['comments'] : '';
            if ($comments !== '') {
                $this->updateOrderComments($comments, $this->orderId, $this->order->getStatus());
            }
            $this->updateAddresses();
            if (!empty($this->shippingComment) || !empty($this->billingComment)) {
                $this->updateComments();
            }
            if (is_numeric($this->orderId)) {
                $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_editOrder';
                $this->eventManager->dispatch($aftereventname, ['orderObject' => $this]);
                return $this->setResponse(
                    \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
                    __("edit_order_success"),
                    $this->order->getIncrementId()
                );
            } else {
                throw new LocalizedException(__("edit_order_runtime_issue"));
            }
        } catch (\Exception $ex) {
            return $this->setResponse(
                \I95DevConnect\MessageQueue\Helper\Data::ERROR,
                $ex->getMessage(),
                null
            );
        }
    }

    /**
     * Update Order addresses if any changes, provided the order status should satisfy allowed conditions.
     *
     * @throws LocalizedException
     */
    public function updateAddresses()
    {
        //CASE-4: Partially Shipped and Partially Invoiced.
        $this->partiallyShipInvoiced();

        //CASE-1: NO Shipment, NO invoice.
        $this->noShipInvoiced();

        //CASE-2: Fully shipped in NAV No Invoiced.(Update allowed for only billing address)
        $this->fullyshippedNoInvoice();

        //CASE-3: Partially shipped in NAV No Invoiced.
        if ($this->order->hasShipments() && $this->order->canShip() &&
            $this->order->canInvoice() && !$this->order->hasInvoices()) {
            if (!$this->isSameShippingAddress) {
                throw new LocalizedException(__($this->orderUpdateErr));
            }

            if (!$this->isSameBillingAddress) {
                $this->updateOrderBillingAddress();
            }
        }
        //CASE-5: Order placed with online payment method from magento.(Update allowed for only shipping address)
        if ($this->order->hasInvoices() && !$this->order->canInvoice() && !$this->order->hasShipments()) {
            if (!$this->isSameBillingAddress) {
                throw new LocalizedException(__('order_update_002'));
            }
            if (!$this->isSameShippingAddress) {
                $this->updateOrderShippingAddress();
            }
        }
    }

    /**
     * Update order billing address with new address
     *
     * @return object
     * @throws LocalizedException
     * @author Debashis S. Gopal. Updated code:
     * If we update both shipping and billing address at a time it is not working,
     * So removed comment update code and added it after all the address update complete.
     */
    public function updateOrderBillingAddress()
    {
        try {
            $billingAddressId = $this->order->getBillingAddress()->getId();
            $previousAddress = $this->orderAddressFactory->create()->load($billingAddressId);
            if ($this->component === 'AX' || $this->component === 'D365FO') {
                $editedBillingAddress = $this->editBillingAddress;
            } else {
                $editedBillingAddress = $this->editOrderHelper->prepareAddress($this->editBillingAddress);
            }

            $this->editOrderValidator->validateAddress($editedBillingAddress);
            $this->updateAddress('billing', $editedBillingAddress);
            $this->billingComment = "Previous " . $previousAddress->getAddressType() . " address: " .
                $this->addressRenderer->format($previousAddress, 'html');
        } catch (\Exception $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
        return $this->order;
    }

    /**
     * Update order shipping address with new address
     *
     * @return object
     * @throws LocalizedException
     * @author Debashis S. Gopal. Updated code:
     * If we update both shipping and billing address at a time it is not working,
     * So removed comment update code and added it after all the address update complete.
     */
    public function updateOrderShippingAddress()
    {
        try {
            $previousAddress = $this->orderAddressFactory->create()->load($this->order->getShippingAddress()->getId());
            $editedShippingAddress = $this->editOrderHelper->prepareAddress($this->editShippingAddress);
            $this->editOrderValidator->validateAddress($editedShippingAddress);
            $this->updateAddress('shipping', $editedShippingAddress);
            $this->shippingComment = "Previous " . $previousAddress->getAddressType() . " address: " .
                $this->addressRenderer->format($previousAddress, 'html');
        } catch (\Exception $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
        return $this->order;
    }

    /**
     * Update address update comments, in order level along and in all shipmentes and invoices level if any.
     *
     * @author Debashis S. Gopal
     * @return void
     */
    public function updateComments()
    {
        try {
            if (!empty($this->shippingComment)) {
                $this->updateOrderComments($this->shippingComment, $this->orderId, $this->order->getStatus());
                $this->shipmentInvoiceComment();
            } elseif (!empty($this->billingComment)) {
                $this->updateOrderComments($this->billingComment, $this->orderId, $this->order->getStatus());
                if ($this->order->hasShipments()) {
                    foreach ($this->order->getShipmentsCollection() as $shipment) {
                        $this->updateShipmentComment($this->billingComment, $shipment->getId());
                    }
                }
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                $ex->getMessage(),
                \I95DevConnect\MessageQueue\Api\LoggerInterface::I95EXC,
                'critical'
            );
        }
    }

    /**
     * Validate customer details sent by ERP.
     */
    public function checkForCustomerData()
    {
        if (isset($this->stringData[self::CUSTOMER]['isGuest']) && $this->stringData[self::CUSTOMER]['isGuest']) {
            $this->customerEmail = $this->stringData[self::CUSTOMER]['email'];
            $this->customerId = '';
        } else {
            $this->editOrderValidator->validateData(
                $this->stringData,
                ['targetCustomerId' => 'targetCustomerId Missing']
            );
            $customerInfo = $this->mqGenericHepler->getCustomerInfoByTargetId($this->stringData['targetCustomerId']);
            if (!empty($customerInfo)) {
                $this->customerId = $customerInfo[0]->getId();
                $this->customerEmail = $customerInfo[0]->getEmail();
            }
        }
    }

    /**
     * Compare ERP given order shipping and billing addresses with existing order addresses.
     */
    public function checkForAddressChanges()
    {
        $orderBillingAddress = $this->order->getBillingAddress();
        $orderShippingAddress = $this->order->getShippingAddress();
        $this->component = $this->dataHelper->getscopeConfig(
            'i95dev_messagequeue/I95DevConnect_settings/component',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
        if ($this->component == 'AX' || $this->component == 'D365FO') {
            $newTargetAddressId = $this->dataHelper->getValueFromArray(
                "targetId",
                $this->stringData[self::BILLING_ADDRESS]
            );
            if (isset($newTargetAddressId) &&
                $this->compareTargetAddressId($orderBillingAddress, $newTargetAddressId)
            ) {
                $this->isSameBillingAddress = false;
                $this->editBillingAddress = $this->mqGenericHepler->getAddressByTargetAddressId(
                    $newTargetAddressId,
                    $this->customerId
                );
            }
        } else {
            $this->editBillingAddress = (isset($this->stringData[self::BILLING_ADDRESS]) ?
                $this->stringData[self::BILLING_ADDRESS] : "");
            if ($this->editBillingAddress !== "") {
                $this->isSameBillingAddress = $this->editOrderHelper->compareAddress(
                    $orderBillingAddress,
                    $this->editBillingAddress
                );
            }
        }
        $this->editShippingAddress = (isset($this->stringData['shippingAddress']) ?
            $this->stringData['shippingAddress'] : "");

        if ($this->editShippingAddress !== "") {
            $this->isSameShippingAddress = $this->editOrderHelper->compareAddress(
                $orderShippingAddress,
                $this->editShippingAddress
            );
        }
    }

    /**
     * Compare target address id
     *
     * @param object $billingAddress
     * @param int $newTargetAddressId
     * @return bool
     * @throws LocalizedException
     */
    public function compareTargetAddressId($billingAddress, $newTargetAddressId)
    {
        $billingAddressId = $billingAddress->getCustomerAddressId();
        $existingTargetAddressId = $this->addressRepo->getById($billingAddressId)
        ->getCustomAttribute('target_address_id')->getvalue();
        if (isset($existingTargetAddressId) && $existingTargetAddressId !== $newTargetAddressId) {
            return true;
        }
        return false;
    }

    /**
     * Update shipping and billing address send by ERP
     *
     * @param string $type
     * @param array $address
     * @throws LocalizedException
     */
    public function updateAddress($type, $address)
    {
        if ($type === 'shipping') {
            $addressId = $this->order->getShippingAddress()->getId();
        } else {
            $addressId = $this->order->getBillingAddress()->getId();
        }
        try {
            $orderAddress = $this->orderAddressRepository->get($addressId);
            $orderAddress->setCity($address["city"])
                ->setCompany($address["company"])
                ->setCountryId($address["country_id"])
                ->setCustomerId($this->customerId)
                ->setFax($address["fax"])
                ->setFirstname($address["firstname"])
                ->setMiddlename($address["middlename"])
                ->setLastname($address["lastname"])
                ->setParentId($this->orderId)
                ->setPostcode($address["postcode"])
                ->setPrefix($address["prefix"])
                ->setRegion($address["region"])
                ->setEmail($this->customerEmail)
                ->setRegionId($address["region_id"])
                ->setStreet($address["street"])
                ->setSuffix($address["suffix"])
                ->setTelephone($address["telephone"]);
            $this->orderAddressRepository->save($orderAddress);
        } catch (\Exception $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Put previous address as comment with invoice Id
     *
     * @param string $comment
     * @param string $invoiceId
     * @throws LocalizedException
     */
    public function updateInvoiceComment($comment, $invoiceId)
    {
        try {
            $invoiceCommentData = $this->invoiceCommentDataFactory->create();
            $invoiceCommentData->setIsCustomerNotified(0)
                ->setParentId($invoiceId)
                ->setComment($comment)
                ->setIsVisibleOnFront(0);
            $this->invoiceCommentRepository->save($invoiceCommentData);
        } catch (\Exception $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Put previous address as comment with shipmentId
     *
     * @param string $comment
     * @param string $shipmentId
     * @throws LocalizedException
     */
    public function updateShipmentComment($comment, $shipmentId)
    {
        try {
            $shipmentCommentData = $this->shipmentCommentDataFactory->create();
            $shipmentCommentData->setIsCustomerNotified(0)
                ->setParentId($shipmentId)
                ->setComment($comment)
                ->setIsVisibleOnFront(0);
            $this->shipmentCommentRepository->save($shipmentCommentData);
        } catch (\Exception $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Partially Shipped and Invoiced
     *
     * @throws LocalizedException
     */
    public function partiallyShipInvoiced()
    {
        if (($this->order->hasInvoices() && $this->order->canInvoice())
            && ($this->order->hasShipments() && $this->order->canShip())
        ) {
            if (!$this->isSameShippingAddress) {
                throw new LocalizedException(__($this->orderUpdateErr));
            }
            if (!$this->isSameBillingAddress) {
                throw new LocalizedException(__('order_update_002'));
            }
        }
    }

    /**
     * No shipment and No Invoice
     *
     * @throws LocalizedException
     */
    public function noShipInvoiced()
    {
        if (($this->order->canInvoice() && !$this->order->hasInvoices())
            && ($this->order->canShip() && !$this->order->hasShipments())
        ) {
            if (!$this->isSameBillingAddress) {
                $this->updateOrderBillingAddress();
            }
            if (!$this->isSameShippingAddress) {
                $this->updateOrderShippingAddress();
            }
        }
    }

    /**
     * Fully Shipped and no Invoice
     *
     * @throws LocalizedException
     */
    public function fullyshippedNoInvoice()
    {
        if ($this->order->hasShipments() && !$this->order->canShip() &&
            $this->order->canInvoice() && !$this->order->hasInvoices()) {
            if (!$this->isSameShippingAddress) {
                throw new LocalizedException(__($this->orderUpdateErr));
            }
            if (!$this->isSameBillingAddress) {
                $this->updateOrderBillingAddress();
            }
        }
    }

    /**
     * Shipment and Invoice Comments
     *
     * @throws LocalizedException
     */
    public function shipmentInvoiceComment()
    {
        if ($this->order->hasInvoices() || $this->order->hasShipments()) {
            foreach ($this->order->getInvoiceCollection() as $invoice) {
                $this->updateInvoiceComment($this->shippingComment, $invoice->getId());
            }
            foreach ($this->order->getShipmentsCollection() as $shipment) {
                $this->updateShipmentComment($this->shippingComment, $shipment->getId());
            }
        }
    }
}
