<?php

namespace Silk\Coker\Helper;

use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResourceModel;

class ContactHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

   /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var RegionResourceModel
     */
    protected $regionResourceModel;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Helper\Context $context,
        RegionFactory $regionFactory,
        RegionResourceModel $regionResourceModel
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->regionFactory = $regionFactory;
        $this->regionResourceModel = $regionResourceModel;

        parent::__construct($context);
    }


    public function getInfo($name)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $info =  $this->scopeConfig->getValue("general/store_information/" . $name, $storeScope);

         if($name == 'region_id') {
            $info = $this->getState($info);
         }

        return $info;
    }

    private function getState($provinceId) {
        if (isset($provinceId)) {
            $magentoRegion = $this->regionFactory->create();
            $this->regionResourceModel->load($magentoRegion, $provinceId);
            $provinceCode = $magentoRegion->getCode();
        } else {
            $provinceCode = null;
        }

        return $provinceCode;
    }
   

}















