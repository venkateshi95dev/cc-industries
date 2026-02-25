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

namespace Ebizcharge\Ebizcharge\Ui\Component\DataProvider;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as CoreDataProvider;

/**
 * Reset Filter Data Provider
 *
 * Class DataProvider
 */
class ResetFilter extends CoreDataProvider
{
    /**
     * Customer Name
     *
     * @const CUSTOMER_NAME
     */
    public const CUSTOMER_NAME = 'customer_name';

    /**
     * Customer Email
     *
     * @const CUSTOMER_EMAIL
     */
    public const CUSTOMER_EMAIL = 'customer_email';

    /**
     * Add Filter
     *
     * @param Filter $filter
     * @return mixed|void
     */
    public function addFilter(Filter $filter)
    {
        if ($filter->getField() === static::CUSTOMER_NAME) {
            $filter = $this->resetFilter($filter);
        }
        parent::addFilter($filter);
    }

    /**
     * This function reset the condition for customer ID and customer name
     *
     * @param Filter $filter
     * @return Filter
     */
    private function resetFilter(Filter $filter): Filter
    {
        $value = trim($filter->getValue(), '%');
        if (is_numeric($value)) {
            $filter->setField(RecurringInterface::MAGE_CUST_ID);
        } elseif (strpos($value, '@') !== false) {
            $filter->setField(static::CUSTOMER_EMAIL);
        }
        return $filter;
    }
}
