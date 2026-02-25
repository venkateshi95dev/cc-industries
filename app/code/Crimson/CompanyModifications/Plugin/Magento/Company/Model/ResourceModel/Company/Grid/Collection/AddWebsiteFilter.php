<?php

namespace Crimson\CompanyModifications\Plugin\Magento\Company\Model\ResourceModel\Company\Grid\Collection;

use Magento\Company\Model\ResourceModel\Company\Grid\Collection;


class AddWebsiteFilter
{
    public function beforeLoad(Collection $subject)
    {
        if (!$subject->isLoaded()) {
            $select = $subject->getSelect();
            $select->columns(['website_id' => 'customer_grid_flat.website_id']);
        }
    }

    public function aroundAddFieldToFilter(
        Collection $subject,
        \Closure $proceed,
        $field,
        $condition = null
    ) {
        if ($field === 'website_id') {
            $subject->getSelect()->where(
                $subject->getConnection()->prepareSqlCondition('customer_grid_flat.website_id', $condition)
            );

            return $subject;
        }

        return $proceed($field, $condition);
    }
}