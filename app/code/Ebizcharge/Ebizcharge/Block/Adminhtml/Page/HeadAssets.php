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

use Ebizcharge\Ebizcharge\Model\ConfigFactory as EbizConfigFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\ProductMetadataFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class HeadAssets extends Template
{

    /**
     * @var ProductMetadataFactory
     */
    protected ProductMetadataFactory $_productMetadata;

    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::page/head-assets.phtml';

    /**
     * @var EbizConfigFactory
     */
    protected EbizConfigFactory $ebizConfigFactory;

    /**
     * @param Context $context
     * @param ProductMetadataFactory $productMetadata
     * @param EbizConfigFactory $ebizConfigFactory
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Context                $context,
        ProductMetadataFactory $productMetadata,
        EbizConfigFactory      $ebizConfigFactory,
        array                  $data = [],
        ?JsonHelper            $jsonHelper = null,
        ?DirectoryHelper       $directoryHelper = null
    )
    {
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);

        /** @var  _productMetadata */
        $this->_productMetadata = $productMetadata;
        /** @var ebizConfigFactory */
        $this->ebizConfigFactory = $ebizConfigFactory;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isModuleActive()
    {
        $storeId = $this->ebizConfigFactory->create()->getStoreId();
        return $this->ebizConfigFactory->create()->isActive($storeId);
    }


}
