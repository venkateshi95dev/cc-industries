<?php

namespace Crimson\Shipping\Model\Carrier;

use Crimson\Shipping\Helper\Data as Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Atypicalusregions
 * @package Crimson\Shipping\Model\Carrier
 */
class Atypicalusregions extends AbstractCarrier implements CarrierInterface
{

    /**
     * @var string
     */
    protected $_code = 'atypicalusregions';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_resultMethodFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $resultMethodFactory,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->_rateResultFactory = $rateResultFactory;
        $this->_resultMethodFactory = $resultMethodFactory;
        $this->helper  = $helper;
    }

    /**
     * @param RateRequest $request
     *
     * @return bool|DataObject|Result|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!$this->helper->isAtypicalShippingAllowed($request)) {
            return false;
        }

        /** @var Result $result */
        $result = $this->_rateResultFactory->create();
        $method = $this->createShippingMethod();
        $result->append($method);

        return $result;
    }

    /**
     * @return Method
     */
    private function createShippingMethod(): Method
    {
        /** @var  Method $method */
        $method = $this->_resultMethodFactory->create();

        $shippingPrice = '0.00';

        $method->setCarrier('atypicalusregions');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('atypicalusregions');
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        return $method;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods() :array
    {
        return ['atypicalusregions' => $this->getConfigData('name')];
    }

    /**
     * @return array
     */
    public function getAtypicalRegionCodes() :array
    {
        return $this->helper->getAtypicalRegionCodes();
    }
}
