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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\Page;

use Ebizcharge\Ebizcharge\Model\Config as EbizConfig;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\ProductMetadataFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Copyright block class
 *
 * Class Copyright
 */
class Copyright extends Template
{
    /**
     * @var ProductMetadataFactory
     */
    protected ProductMetadataFactory $_productMetadata;

    /**
     * @var TranApi
     */
    protected TranApi $_soapApiModel;

    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::support/copyright.phtml';

    /**
     * @var EbizConfig
     */
    protected EbizConfig $_ebizConfig;

    /**
     * @param Context $context
     * @param ProductMetadataFactory $productMetadata
     * @param TranApi $tranApi
     * @param EbizConfig $ebizConfig
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Context $context,
        ProductMetadataFactory $productMetadata,
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

        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * Ebizcharge Version
     *
     * @return string
     */
    public function getEbizchargeVersion()
    {
        return $this->escapeHtml(__('EBizCharge Version %1', TranApi::EBIZCHARGE_VERSION));
    }

    /**
     * Get Magento Version
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        $productMetadata = $this->_productMetadata;
        return $this->escapeHtml(__('ver. %1', $productMetadata->getVersion()));
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
}
