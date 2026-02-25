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

namespace Ebizcharge\Ebizcharge\Model\Adapter;

use Magento\Framework\ObjectManagerInterface;

/**
 * Ebizcharge adapter instance creation
 *
 * Class EbizchargeAdapterFactory
 */
class EbizchargeAdapterFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * Class Main Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        /** @var $objectManager */
        $this->objectManager = $objectManager;
    }

    /**
     * Create credentials and place order
     *
     * @param mixed $storeId
     * @return mixed
     */
    public function create($storeId = null)
    {
        return $this->objectManager->create(
            EbizchargeAdapter::class
        );
    }
}
