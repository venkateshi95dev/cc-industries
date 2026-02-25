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

namespace Ebizcharge\Ebizcharge\Block\Customer\Account;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Block\Address\Edit;
use Magento\Customer\Helper\Address;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;
use \Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;

/**
 * Add Payment Methods block class
 *
 * Class AddPaymentMethods
 */
class AddPaymentMethods extends Edit
{
    /**
     * @var Data
     */
    protected Data $_directoryHelper;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var Config
     */
    protected $_configCacheType;

    /**
     * @var RegionCollectionFactory
     */
    protected $_regionCollectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    protected AddressInterfaceFactory $_addressDataFactory;

    /**
     * @var CurrentCustomer
     */
    protected CurrentCustomer $_currentCustomer;

    /**
     * @var DataObjectHelper
     */
    protected DataObjectHelper $_dataObjectHelper;

    /**
     * AddPaymentMethods constructor.
     *
     * @param Context $context
     * @param Data $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param Config $configCacheType
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param CollectionFactory $countryCollectionFactory
     * @param Session $customerSession
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CurrentCustomer $currentCustomer
     * @param DataObjectHelper $dataObjectHelper
     * @param array $data
     * @param AddressMetadataInterface|null $addressMetadata
     * @param Address|null $addressHelper
     */
    public function __construct(
        Context $context,
        Data $directoryHelper,
        EncoderInterface $jsonEncoder,
        Config $configCacheType,
        RegionCollectionFactory $regionCollectionFactory,
        CollectionFactory $countryCollectionFactory,
        Session $customerSession,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        CurrentCustomer $currentCustomer,
        DataObjectHelper $dataObjectHelper,
        array $data = [],
        AddressMetadataInterface $addressMetadata = null,
        Address $addressHelper = null
    ) {
        /** parent Construct */
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $customerSession,
            $addressRepository,
            $addressDataFactory,
            $currentCustomer,
            $dataObjectHelper,
            $data,
            $addressMetadata,
            $addressHelper
        );

        /** @var _directoryHelper */
        $this->_directoryHelper = $directoryHelper;
        /** @var _jsonEncoder */
        $this->_jsonEncoder = $jsonEncoder;
        /** @var _configCacheType */
        $this->_configCacheType = $configCacheType;
        /** @var _regionCollectionFactory */
        $this->_regionCollectionFactory = $regionCollectionFactory;
        /** @var _countryCollectionFactory */
        $this->_countryCollectionFactory = $countryCollectionFactory;
        /** @var _customerSession */
        $this->_customerSession = $customerSession;
        /** @var _addressRepository */
        $this->_addressRepository = $addressRepository;
        /** @var _addressDataFactory */
        $this->_addressDataFactory = $addressDataFactory;
        /** @var _currentCustomer */
        $this->_currentCustomer = $currentCustomer;
        /** @var _dataObjectHelper */
        $this->_dataObjectHelper = $dataObjectHelper;
    }
}
