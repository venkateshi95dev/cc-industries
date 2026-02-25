<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Salesrule\Model\ResourceModel\Coupon as CouponResource;
use Magento\Salesrule\Model\CouponFactory;

class CleanupCouponCodes
{


    public function __construct(
        private readonly StoreRepositoryInterface $storeRepositoryInterface,
        private readonly CouponResource $couponResource,
        private readonly CouponFactory $couponFactory,
    ) {}


    public function execute(): array
    {
        $result['message'] = "Cleaned up coupon codes.";
        $this->_cleanupCouponCodes();
        return $result;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function _cleanupCouponCodes(): void
    {
        $connection = $this->couponResource->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->couponResource->getTable('salesrule')])
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.rule_id'])
        ;
        $data = $connection->fetchAll($select);
        if (!$data) {
            $data = [];
        }

        if (!empty($data)) {
            $connection->beginTransaction();
            $connection->delete(
                $this->couponResource->getMainTable(),
                $connection->quoteInto('rule_id NOT IN (?)', [$data])
            );
            $connection->commit();
        }
    }
}
