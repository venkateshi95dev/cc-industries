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

namespace Ebizcharge\Ebizcharge\ViewModel;

use Magento\Customer\Helper\Address as CustomerAddressHelper;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Data\Helper\PostHelper as SalesPostHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Helper\Reorder as SalesReorderHelper;

/**
 * View Model class for customer account templates
 *
 * Class CustomerAccount
 */
class CustomerAccount implements ArgumentInterface
{
    /**
     * @var DirectoryHelper
     */
    protected DirectoryHelper $_directoryHelper;

    /**
     * @var SalesPostHelper
     */
    protected SalesPostHelper $_salesPostHelper;

    /**
     * @var SalesReorderHelper
     */
    protected SalesReorderHelper $_salesReorderHelper;

    /**
     * @var CustomerAddressHelper
     */
    protected CustomerAddressHelper $_customerAddressHelper;

    /**
     * @param DirectoryHelper $directoryHelper
     * @param SalesPostHelper $salesPostHelper
     * @param SalesReorderHelper $salesReorderHelper
     * @param CustomerAddressHelper $customerAddressHelper
     */
    public function __construct(
        DirectoryHelper       $directoryHelper,
        SalesPostHelper       $salesPostHelper,
        SalesReorderHelper    $salesReorderHelper,
        CustomerAddressHelper $customerAddressHelper,
    ) {
        $this->_directoryHelper = $directoryHelper;
        $this->_salesPostHelper = $salesPostHelper;
        $this->_salesReorderHelper = $salesReorderHelper;
        $this->_customerAddressHelper = $customerAddressHelper;
    }

    /**
     * Get Magento Directory module helper
     *
     * @return DirectoryHelper
     */
    public function getDirectoryHelper()
    {
        return $this->_directoryHelper;
    }

    /**
     * Get Magento Sales module ReOrder helper
     *
     * @return SalesReorderHelper
     */
    public function getSalesReorderHelper()
    {
        return $this->_salesReorderHelper;
    }

    /**
     * Get Magento Sales module PostHelper
     *
     * @return SalesPostHelper
     */
    public function getSalesPostHelper()
    {
        return $this->_salesPostHelper;
    }

    /**
     * Get Magento Customer module Address helper
     *
     * @return CustomerAddressHelper
     */
    public function getCustomerAddressHelper()
    {
        return $this->_customerAddressHelper;
    }
}
