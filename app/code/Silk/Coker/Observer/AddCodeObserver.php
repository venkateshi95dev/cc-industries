<?php
namespace Silk\Coker\Observer;

use Magento\Framework\Event\ObserverInterface;


class AddCodeObserver implements ObserverInterface
{

    public function __construct(
        private readonly \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
    }
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$code = $this->scopeConfig->getValue("finderattribute/header_code/code", $storeScope);
		$response = $observer->getResponse();
		$response->setHeader("custom-code",$code);
	}

}
