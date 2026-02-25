<?php

namespace Silk\Payment\Helper;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Area;
/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PAYMENT_HOST_NAME="payment/silk_payment/hostname";
    const XML_PAYMENT_API_URL ="payment/silk_payment/apiurl";
    const CONFIG_MODULE_PATH = "payment/silk_payment";

    protected $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        $this->objectManager = $objectManager;
        parent::__construct($context);
    }


    public function getGatewayConfig($code, $storeId = null)
    {
        return $this->getModuleConfig('gateway_config/' . $code, $storeId);
    }

    public function getCredentialsConfig($code, $storeId = null)
    {
        return $this->getModuleConfig('credentials/' . $code, $storeId);
    }

    public function getHostName(){
        return (string)$this->getConfigValue(self::XML_PAYMENT_HOST_NAME);
    }

    public function getApiUrl(){
        return  (string)$this->getConfigValue(self::XML_PAYMENT_API_URL);
    }

    public function genHmac($msg,$secret)
    {
        $algorithm = 'sha256';
        $hexEncodedHash = hash_hmac($algorithm, $msg, $secret);
        $base64EncodedHash = base64_encode($hexEncodedHash);
        return $base64EncodedHash;
    }

    /**
     * @param string $field
     * @param null $storeId
     *
     * @return mixed
     */
    public function getModuleConfig($field = '', $storeId = null)
    {
        $field = ($field !== '') ? '/' . $field : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . $field, $storeId);
    }

        /**
     * @param $field
     * @param null $scopeValue
     * @param string $scopeType
     *
     * @return array|mixed
     */
    public function getConfigValue($field, $scopeValue = null, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        if (!$this->isArea() && is_null($scopeValue)) {
            /** @var Config $backendConfig */
            if (!$this->backendConfig) {
                $this->backendConfig = $this->objectManager->get('Magento\Backend\App\ConfigInterface');
            }

            return $this->backendConfig->getValue($field);
        }

        return $this->scopeConfig->getValue($field, $scopeType, $scopeValue);
    }
    /**
     * @param string $area
     *
     * @return mixed
     */
    public function isArea($area = Area::AREA_FRONTEND)
    {
        if (!isset($this->isArea[$area])) {
            /** @var State $state */
            $state = $this->objectManager->get('Magento\Framework\App\State');

            try {
                $this->isArea[$area] = ($state->getAreaCode() == $area);
            } catch (Exception $e) {
                $this->isArea[$area] = false;
            }
        }

        return $this->isArea[$area];
    }
}
