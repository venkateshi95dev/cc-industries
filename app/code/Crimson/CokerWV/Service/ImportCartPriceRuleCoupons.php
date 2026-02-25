<?php

namespace Crimson\CokerWV\Service;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;
use Magento\SalesRule\Api\Data\CouponInterfaceFactory;


class ImportCartPriceRuleCoupons
{

    const CSV_FILE_PATH = '/import/coker_zip_data_migration/coupon_codes/';

    public function __construct(
        private readonly Csv $csvReader,
        private readonly DirectoryList $directoryList,
        private readonly File $file,
        private readonly RuleRepositoryInterface $ruleRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly CouponInterfaceFactory                        $couponInterfaceFactory,
        private readonly CouponRepositoryInterface                     $couponRepository,
        private readonly LoggerInterface                     $logger
    ) {}


    public function execute($name, $filename)
    {
        $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH.$filename;
        if (!$this->file->isExists($csvPath)) {
            throw new \Exception("Error with the CSV file.");
        }
        $rows = $this->csvReader->getData($csvPath);
        $ruleId = $this->getRuleId($name);
        if (!$ruleId) {
            throw new \Exception("Cart price rule not found");
        }
        foreach ($rows as $num=>$couponData){
            if($num==0)
                continue;
            /** @var CouponInterface $couponCode */
            $couponCode = $this->couponInterfaceFactory->create();
            $couponCode->setRuleId($ruleId)
                ->setCode($couponData[0])
                ->setType(1)
                ->setCreatedAt($couponData[1])
                ->setTimesUsed($couponData[3]);
            try {
                $this->couponRepository->save($couponCode);
            }
            catch (\Exception $e)
            {
                $this->logger->debug($e);
            }
        }
    }

    private function getRuleId($name)
    {
        if ($name) {
            $this->searchCriteriaBuilder->addFilter('name', $name, 'eq');
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $rules = $this->ruleRepository->getList($searchCriteria);
            foreach($rules->getItems() as $rule){
                return $rule->getRuleId();
            }
            return null;
        }
    }

}
