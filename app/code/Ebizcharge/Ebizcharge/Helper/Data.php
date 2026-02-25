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

namespace Ebizcharge\Ebizcharge\Helper;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Exception;
use Magento\Backend\Helper\Data as BackendDataHelper;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Helper Data class
 *
 * Class Data
 */
class Data extends AbstractHelper
{
    /**
     * Config xml path constants
     *
     * @const XML_PATH_REQUEST_CARD_CODE_ADMIN
     */
    public const XML_PATH_REQUEST_CARD_CODE_ADMIN = 'payment/ebizcharge_ebizcharge/request_card_code_admin';

    /**
     * @const XML_PATH_CCTYPES
     */
    public const XML_PATH_CCTYPES = 'payment/ebizcharge_ebizcharge/cctypes';

    /**
     * @var State
     */
    protected State $_appState;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;

    /**
     * @var BackendDataHelper
     */
    protected BackendDataHelper $_dataHelper;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $_urlInterface;

    /**
     * @var array
     */
    protected array $_data;

    /**
     * @var Http
     */
    protected Http $_httpRequest;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param EbizchargeLogger $ebizchargeLogger
     * @param StoreManagerInterface $storeManager
     * @param Http $httpRequest
     * @param BackendDataHelper $dataHelper
     * @param UrlInterface $urlInterface
     * @param array $data
     */
    public function __construct(
        Context $context,
        EbizchargeLogger $ebizchargeLogger,
        StoreManagerInterface $storeManager,
        Http $httpRequest,
        BackendDataHelper $dataHelper,
        UrlInterface $urlInterface,
        array $data = []
    ) {
        /** parent constructor */
        parent::__construct($context);

        /** @var ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _storeManager */
        $this->_storeManager = $storeManager;
        /** @var _dataHelper */
        $this->_dataHelper = $dataHelper;
        /** @var  _data */
        $this->_data = $data;
        /** @var  _urlInterface */
        $this->_urlInterface = $urlInterface;
        /** @var _httpRequest */
        $this->_httpRequest = $httpRequest;
    }

    /**
     * HTTP Request
     *
     * @return Http
     */
    public function getRequest()
    {
        return $this->_httpRequest;
    }

    /**
     * Get Request CardCode Admin
     *
     * @param int|null $scopeCode
     * @param string $scopeType
     * @return mixed
     */
    public function getRequestCardCodeAdmin(int $scopeCode = null, string $scopeType = 'website')
    {
        return $this->getConfigValue(self::XML_PATH_REQUEST_CARD_CODE_ADMIN, $scopeCode, $scopeType);
    }

    /**
     * Get config value by path and scope etc
     *
     * @param mixed $scopePath
     * @param int|null $scopeCode
     * @param string $scopeType
     * @return mixed
     */
    public function getConfigValue($scopePath, int $scopeCode = null, string $scopeType = 'website')
    {
        return $this->scopeConfig->getValue($scopePath, $scopeType, $scopeCode);
    }

    /**
     * GET PAYMENT CC TYPES
     *
     * @param int|null $scopeCode
     * @param string $scopeType
     * @return mixed
     */
    public function getPaymentCctypes(int $scopeCode = null, string $scopeType = 'website')
    {
        return $this->getConfigValue(self::XML_PATH_CCTYPES, $scopeCode, $scopeType);
    }

    /**
     * Get Admin URL
     *
     * @param null|mixed $param
     * @return string
     */
    public function getUrl($param = null): string
    {
        return $this->_dataHelper->getUrl($param);
    }

    /**
     * Get Admin Front Name
     *
     * @return string
     */
    public function getFrontName(): string
    {
        return $this->_dataHelper->getAreaFrontName();
    }

    /**
     * Get Store
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Get Base URL
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->_urlInterface->getBaseUrl();
    }



    /**
     * Get Card Image Path
     *
     * @param string|null $cardType
     * @return string
     */
    public function getCardImagePath(String $cardType = null)
    {
        $imagePath = '';

        if ($image = $this->getCCTypeImage($cardType)) {
            $imagePath = 'Ebizcharge_Ebizcharge::images/cards/' . $image;
        }
        return $imagePath;
    }

    /**
     * Get CC TYPE Image
     *
     * @param mixed $cardType
     * @return string|null
     */
    public function getCCTypeImage($cardType)
    {
        $image = null;

        switch (strtolower($cardType)) {
            case 'v':
            case 'vi':
                $image = 'visa.png';
                break;
            case 'ae':
            case 'a':
                $image = 'american_express.png';
                break;
            case 'mc':
            case 'm':
                $image = 'mastercard.png';
                break;
            case 'ds':
                $image = 'discover.png';
                break;
            case 'jcb':
            case 'j':
                $image = 'jcb.png';
                break;
            case 'ach':
                $image = 'bank-account.png';
                break;
            default:
                $image = 'credit-card.png';
                break;
        }

        return $image;
    }

    /**
     * Get card name with card code
     *
     * @param string $cardCode
     * @return string
     */
    public function getCardName($cardCode)
    {
        $cardName = $cardCode;

        switch (strtolower($cardCode)) {
            case 'v':
            case 'vi':
                $cardName = 'Visa';
                break;
            case 'jcb':
            case 'j':
                $cardName = 'JCB';
                break;
            case 'ae':
            case 'a':
                $cardName = 'American Express';
                break;
            case 'mc':
            case 'm':
                $cardName = 'Master Card';
                break;
            case 'ds':
                $cardName = 'Discover';
                break;
            case 'ach':
                $cardName = 'ACH';
                break;
            default:
                $image = 'Credit Card';
                break;
        }

        return $cardName;
    }

    /**
     * Get Image tag for card
     *
     * @param string $imageViewUrl
     * @param string $type
     * @return string
     */
    public function getImageTag($imageViewUrl, $type = 'ach')
    {
        $imageWidth = 'width: 25px';
        $type == 'ach' && $imageWidth = 'width: 25px';

        return '<img style="vertical-align: middle; ' . $imageWidth . '" src="' .
            $imageViewUrl . '" alt="Payment Card" />';
    }

    /**
     * Get relevant active link for customer subscriptions
     *
     * @return string
     */
    public function getActiveLink(): string
    {
        $actionName = $this->_httpRequest->getActionName();
        $moduleName = $this->_httpRequest->getModuleName();

        if ($moduleName != 'ebizcharge') {
            $actionName = 'index';
        } elseif ($actionName != 'index' || $actionName != 'history') {
            $actionName = 'index';
        }

        return 'ebizcharge/recurrings/' . $actionName;
    }
    /**
     * Check is Admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        try {
            return self::getAppState()->getAreaCode() === Area::AREA_ADMINHTML;
        } catch (Exception $e) {
            $this->_ebizchargeLogger->addError(__($e->getMessage()));
            return false;
        }
    }

    /**
     * @return mixed
     */
    public static function getAreaCode(){
        return self::getAppState()->getAreaCode();
    }

    /**
     * @return mixed
     */
    public static function getAppState()
    {
        return self::getObjectManager(State::class);
    }

    /**
     * @param string|null $className
     * @return mixed
     */
    public static function getObjectManager(string $className = null)
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->get($className);
    }

}
