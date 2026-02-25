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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\SyncAssets;

use Ebizcharge\Ebizcharge\Model\Config as EbizConfig;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\ProductMetadataFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory as StoresCollectionFactory;
use Magento\Store\Model\ResourceModel\Website\Collection;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsitesCollectionFactory;

/**
 * Sync Resources block class
 *
 * Class SyncResources
 */
class SyncResources extends Template
{
    /**
     * @var ProductMetadataFactory
     */
    protected $_productMetadata;

    /**
     * @var TranApi
     */
    protected $_soapApiModel;

    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::support/copyright.phtml';

    /**
     * @var EbizConfig
     */
    protected $_ebizConfig;

    /**
     * @var StoresCollectionFactory
     */
    protected StoresCollectionFactory $_storesCollectionFactory;

    /**
     * @var WebsitesCollectionFactory
     */
    protected WebsitesCollectionFactory $_websitesCollectionFactory;

    /**
     * @param Context $context
     * @param ProductMetadataFactory $productMetadata
     * @param WebsitesCollectionFactory $websiteCollectionFactory
     * @param StoresCollectionFactory $storeCollectionFactory
     * @param TranApi $tranApi
     * @param EbizConfig $ebizConfig
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Context $context,
        ProductMetadataFactory $productMetadata,
        WebsitesCollectionFactory $websiteCollectionFactory,
        StoresCollectionFactory $storeCollectionFactory,
        TranApi $tranApi,
        EbizConfig $ebizConfig,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        /** @var  _productMetadata */
        $this->_productMetadata = $productMetadata;
        /** @var _soapApiModel */
        $this->_soapApiModel = $tranApi;
        /** @var  _ebizConfig */
        $this->_ebizConfig = $ebizConfig;
        /** @var  _storesCollectionFactory */
        $this->_storesCollectionFactory = $storeCollectionFactory;
        /** @var  _websitesCollectionFactory */
        $this->_websitesCollectionFactory = $websiteCollectionFactory;

        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * To check Is EbizCharge module enabled/disabled
     *
     * @return bool
     */
    public function isEbizChargeEnabled()
    {
        return $this->_ebizConfig->isActive();
    }

    /**
     * Get All Stores
     *
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getAllStores()
    {
        return $this->_storesCollectionFactory->create();
    }

    /**
     * Get All Websites
     *
     * @return Collection
     */
    public function getAllWebsites()
    {
        return $this->getWebsiteCollection();
    }

    /**
     * Get Website Collection
     *
     * @return Collection
     */
    public function getWebsiteCollection()
    {
        return $this->_websitesCollectionFactory->create();
    }
}
