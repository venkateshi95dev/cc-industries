<?php

namespace Silk\Coker\Cron;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\DataObject;
use Magento\Quote\Model\QuoteFactory;
use Magento\Reports\Model\ResourceModel\Quote\Collection;
use Magento\Store\Api\StoreRepositoryInterface;
use Silk\Coker\Helper\Data;

class SendEmail
{

    CONST STORES_TO_CHECK = [CokerStoreInterface::COKER_STORE_CODE];

    public function __construct(
        private readonly Collection $quoteCollection,
        private readonly \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        private readonly QuoteFactory $quoteFactory,
        private readonly CustomerFactory $customerFactory,
        private readonly AddressFactory $addressFactory,
        private readonly Data $cokerHelper,
        private readonly StoreRepositoryInterface $storeRepository
    ) {}

	public function execute(): void
    {
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/silk_sendemail.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);

		$now = new \DateTime();
		$nowday = $now->format('Y-m-d H:i:s');
		$logger->info($nowday);
		$time = strtotime($nowday) - (3600*24);
		$yesterday = date("Y-m-d H:i:s",$time);

        $storeIds = $this->getStoreIdsFromStoreCodesToCheck();
        if (!$storeIds) {
            return;
        }

		$items = $this->quoteCollection
            ->prepareForAbandonedReport($storeIds)
		    ->addFieldToFilter("updated_at", ['lteq' => $nowday])
		    ->addFieldToFilter("updated_at", ['gteq' => $yesterday]);

		$objs = [];
		if(count($items)) {
			$logger->info('count items:' . count($items));
		     foreach ($items as $k => $item) {
		         $quote = $this->quoteFactory->create()->loadByIdWithoutStore($item->getId());
		         if ($quote && $quote->getId()) {
		             $obj = new DataObject();
		             $email = $item->getCustomerEmail();
		             $customer_firstname = $item->getCustomerFirstname();
		             $customer_lastname = $item->getCustomerLastname();
		             $name = $customer_firstname." ".$customer_lastname;
		             $obj->setEmail($email);
		             $obj->setCustomerName($name);
		             $shippingAddress = $quote->getShippingAddress();
		             if ($shippingAddress && $shippingAddress->getId() &&  $shippingAddress->getData('street')) {
		                 $street = $shippingAddress->getData('street');
		                 $countryCode = $shippingAddress->getData('country_id');
		                 $telephone = $shippingAddress->getData('telephone');
		                 $obj->setStreet($street);
		                 $obj->setCountryCode($countryCode);
		                 $obj->setTelephone($telephone);
		             } else {
						$customerModel = $this->customerFactory->create()->setWebsiteId("1")->loadByEmail($email);
						if ($customerModel && $customerModel->getId()) {
                            if ($customerModel->getDefaultBilling()) {
                                 $billingAddressId = $customerModel->getDefaultBilling();
                                 $billingAddress = $this->addressFactory->create()->load($billingAddressId);
                                 $street = $billingAddress->getData('street');
                                 $countryCode = $billingAddress->getData('country_id');
                                 $telephone = $billingAddress->getData('telephone');
                                 $obj->setStreet($street);
                                 $obj->setCountryCode($countryCode);
                                 $obj->setTelephone($telephone);
                            } else if($customerModel->getAddresses()) {
                                 $customerAddress = [];
                                 foreach ($customerModel->getAddresses() as $address) {
                                     $customerAddress[] = $address->toArray();
                                 }

                                 foreach ($customerAddress as $customerAddres) {
                                     $street =  $customerAddres['street'];
                                     $countryCode = $customerAddres['country_id'];
                                     $telephone = $customerAddres["telephone"];
                                     $obj->setStreet($street);
                                     $obj->setCountryCode($countryCode);
                                     $obj->setTelephone($telephone);
                                 }

                            } else {
                                 $obj->setStreet("");
                                 $obj->setCountryCode("");
                                 $obj->setTelephone("");
                            }
						} else {
						 $obj->setStreet("");
						 $obj->setCountryCode("");
						 $obj->setTelephone("");
						}
		             }

		             $getAllItems = $quote->getAllItems();
		             $arr = array();
		             foreach ($getAllItems as $key => $value) {
		                 $prodobj = new DataObject();
		                 $name =  $value->getName();
		                 $qty =  $value->getQty();
		                 $sku =  $value->getSku();
		                 $price =  $value->getPrice();
		                 $price = $this->pricingHelper->currency($price,true,false);
		                 $total = $value->getRowTotal();
		                 $total = $this->pricingHelper->currency($total,true,false);
		                 $prodobj->setName($name);
		                 $prodobj->setSku($sku);
		                 $prodobj->setPrice($price);
		                 $prodobj->setQty($qty);
		                 $prodobj->setTotal($total);
		                 $arr[] = $prodobj;
		             }
		             $obj->setProds($arr);
		             $objs[] = $obj;
		         }
		     }

			 $logger->info('end foreach, prepare import and send');

			if (count($objs)) {
				$logger->info('start to import');
				try{
					$filename =  $this->cokerHelper->importDatatoCsv($objs);
					$logger->info('file name: '. $filename);
                    $this->cokerHelper->sendEmail($filename,$objs);
				} catch(\Exception $exception) {
					$logger->info("error: " . $exception->getMessage());
				}
			}
		}
	}

    private function getStoreIdsFromStoreCodesToCheck(): array
    {
        $result = [];
        foreach (self::STORES_TO_CHECK as $storeCode) {
            $storeId = $this->getStoreCodeById($storeCode);
            if ($storeId) {
                $result[] = $storeId;
            }
        }

        return $result;
    }

    private function getStoreCodeById($storeCode): ?int
    {
        try {
            return $this->storeRepository->get($storeCode)->getId();
        } catch (\Exception $e) {
            return null;
        }
    }
}
