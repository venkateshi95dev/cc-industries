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

namespace Ebizcharge\Ebizcharge\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface FutureSubscriptionSearchInterface
 *
 * Future Subscription Search Data Interface
 */
interface FutureSubscriptionSearchInterface extends SearchResultsInterface
{
    /**
     * Get future subscription list
     *
     * @return ExtensibleDataInterface[]
     */
    public function getItems();

    /**
     * Set future subscription list
     *
     * @param array $items
     * @return FutureSubscriptionSearchInterface
     */
    public function setItems(array $items);
}
