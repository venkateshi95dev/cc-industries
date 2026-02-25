<?php

namespace Crimson\Checkout\Controller\Address;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ResourceModel\Quote\Address as AddressResource;

/**
 * Class Validate
 * @package Crimson\Checkout\Controller\Address
 */
class Validate extends Action
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var AddressResource
     */
    protected $addressResource;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Session $session,
        AddressResource $addressResource
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        $this->addressResource = $addressResource;
    }

    /**
     * @return Json
     * @throws LocalizedException
     */
    public function execute(): Json
    {
        $request = $this->getRequest();
        $addressData = $this->getRequest()->getPostValue();

        $responseData = [
            'error' => true,
            'message' => ''
        ];

        //Validating request
        if (!$request->isAjax()) {
            throw new LocalizedException(__('Invalid request.'));
        }

        try {
            $shippingAddress = $this->_getQuoteShippingAddress();
            if (!$shippingAddress) {
                throw new LocalizedException(__('It is not possible to load your quote address.'));
            }

            $shippingAddress->setStreet($addressData["street"]);
            $shippingAddress->setCity($addressData["city"]);
            $shippingAddress->setCountryId($addressData["country_id"]);
            $shippingAddress->setPostcode($addressData["postcode"]);
            $shippingAddress->setRegion($addressData["region"]);
            $shippingAddress->setRegionId($addressData["region_id"]);

            //validation fields
            $shippingAddress->getExtensionAttributes()->setShipAdv($addressData["ship_adv"]);
            $shippingAddress->getExtensionAttributes()->setShipAdvDate($addressData["ship_adv_date"]);
            $shippingAddress->getExtensionAttributes()->setShipAdvDi($addressData["ship_adv_di"]);
            $shippingAddress->getExtensionAttributes()->setShipAdvDpi($addressData["ship_adv_dpi"]);

            $this->addressResource->save($shippingAddress);

            $responseData['error'] = false;
        } catch (\Exception $e) {
            $responseData['message'] = __('We can\'t update the address right now: %1', $e->getMessage());
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($responseData);

        return $resultJson;
    }

    /**
     * @return Address|null
     */
    protected function _getQuoteShippingAddress(): ?Address
    {
        try {
            $quote = $this->session->getQuote();
            if ($quote && $quote->getShippingAddress()) {
                return $quote->getShippingAddress();
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
