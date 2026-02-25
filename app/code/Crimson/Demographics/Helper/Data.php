<?php

namespace Crimson\Demographics\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package Crimson\Demographics\Helper
 */
class Data extends AbstractHelper {

    const XPATH_ENABLED = 'demographics/demographics/enabled';
    const XPATH_REQUIRED = 'demographics/demographics/required';
    const XPATH_MESSAGE = 'demographics/demographics/message';
    const XPATH_MAPPING = 'demographics/demographics/mapping';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer
    ){
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * @param null $scope
     * @return bool
     */
    public function isEnabled($scope = null): bool
    {
        return $this->scopeConfig->isSetFlag($this->getEnabled(), ScopeInterface::SCOPE_STORE, $scope);
    }
    /**
     * @param null $scope
     * @return bool
     */
    public function isRequired($scope = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_REQUIRED, ScopeInterface::SCOPE_STORE, $scope);
    }

    /**
     * @return string
     */
    public function getEnabled(): string
    {
        return self::XPATH_ENABLED;
    }

    /**
     * @return string
     */
    public function getDemographicsMessage(): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_MESSAGE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null $scope
     * @return array|bool|float|int|null|string
     */
    public function getMappingConfig($scope = null)
    {
        return $this->serializer->unserialize($this->scopeConfig->getValue(self::XPATH_MAPPING, ScopeInterface::SCOPE_STORE, $scope));
    }
}
