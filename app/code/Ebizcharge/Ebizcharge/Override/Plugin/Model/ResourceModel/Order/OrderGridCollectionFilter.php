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

namespace Ebizcharge\Ebizcharge\Override\Plugin\Model\ResourceModel\Order;

use Closure;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

//use Magento\Sales\Plugin\Model\ResourceModel\Order\OrderGridCollectionFilter as OrigOrderGridCollectionFilter;

/**
 * Order Grid Collection Filter Preference on Plugin
 *
 * Class OrderGridCollectionFilter
 */
class OrderGridCollectionFilter
{
    protected ConfigFactory $configFactory;
    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timeZone;

    /**
     * Timezone converter interface
     *
     * @param TimezoneInterface $timeZone
     * @param ConfigFactory $configFactory
     */
    public function __construct(
        TimezoneInterface $timeZone,
        ConfigFactory     $configFactory
    )
    {
        /** @var $timeZone */
        $this->timeZone = $timeZone;
        /** @var $configFactory */
        $this->configFactory = $configFactory;

    }

    /**
     * Conditional column filters with timezone convertor interface
     *
     * @param SearchResult $subject
     * @param Closure $proceed
     * @param string $field
     * @param string|null $condition
     * @return SearchResult|mixed
     * @throws LocalizedException
     */
    public function aroundAddFieldToFilter(
        SearchResult $subject,
        Closure     $proceed,
                     $field,
                     $condition = null
    )
    {

        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if ($isEbizChargeActive) {
            if ($field === 'created_at' || $field === 'order_created_at') {
                $field = "main_table.created_at";
                if (is_array($condition)) {
                    foreach ($condition as $key => $value) {
                        $condition[$key] = $this->timeZone->convertConfigTimeToUtc($value);
                    }
                }
                $fieldName = $subject->getConnection()->quoteIdentifier($field);
                $condition = $subject->getConnection()->prepareSqlCondition($fieldName, $condition);
                $subject->getSelect()->where($condition, null, Select::TYPE_CONDITION);
                return $subject;
            }
        }

        return $proceed($field, $condition);
    }
}
