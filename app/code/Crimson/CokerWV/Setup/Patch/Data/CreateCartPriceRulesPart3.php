<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\SalesRule\Api\Data\ConditionInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\Data\RuleInterfaceFactory;
use Magento\SalesRule\Api\Data\CouponInterfaceFactory;
use Magento\SalesRule\Api\Data\ConditionInterfaceFactory;
use Magento\SalesRule\Model\Rule\Condition\Combine;
use Amasty\Conditions\Model\Rule\Condition\CustomerAttributes;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use Magento\SalesRule\Model\Rule\Condition\Product\Subselect;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area as AppArea;
use Magento\Framework\App\State as AppState;
use Psr\Log\LoggerInterface;

class CreateCartPriceRulesPart3 implements DataPatchInterface
{

    public function __construct(
        private readonly StoreManagerInterface                         $storeManager,
        private readonly RuleInterfaceFactory                          $ruleInterfaceFactory,
        private readonly ConditionInterfaceFactory                     $conditionInterfaceFactory,
        private readonly RuleRepositoryInterface                       $ruleRepository,
        private readonly CouponRepositoryInterface                     $couponRepository,
        private readonly CouponInterfaceFactory                        $couponInterfaceFactory,
        private readonly \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        private readonly AppState                                      $appState,
        private readonly LoggerInterface                               $logger
    )
    {
    }

    public function apply(): void
    {
        $this->appState->emulateAreaCode(
            AppArea::AREA_ADMINHTML,
            function () {
                $this->createCartRuleBFG10Off();
                $this->createCartRuleAMCA10Discount();
                $this->createCartRuleCleanerDressingFreeship();
            }
        );
    }

    private function createCartRuleBFG10Off()
    {
        $cokerWebsiteId = $this->storeManager->getWebsite(CokerStoreInterface::COKER_WEBSITE_CODE)->getId();
        $newRule = $this->ruleInterfaceFactory->create();
        $newRule->setName('BFG10%OFF')
            ->setDescription('BFG10%OFF')
            ->setIsAdvanced(1)
            ->setStopRulesProcessing(0)
            ->setCustomerGroupIds([0, 1, 2, 3])
            ->setWebsiteIds([$cokerWebsiteId])
            ->setIsRss(1)
            ->setUsesPerCoupon(1000)
            ->setUsesPerCustomer(1000)
            ->setDiscountStep(0)
            ->setDiscountQty(0)
            ->setCouponType(RuleInterface::COUPON_TYPE_SPECIFIC_COUPON)
            ->setSimpleAction(RuleInterface::DISCOUNT_ACTION_BY_PERCENT)
            ->setDiscountAmount(10)
            ->setSortOrder(1)
            ->setIsActive(1)
            ->setSimpleFreeShipping(1);

        $typeAttribute = $this->attributeRepository->get('4', 'coker_product_type');
        $typeOptionId = $typeAttribute->getSource()->getOptionId('Tire');

        $tireSeriesAttribute = $this->attributeRepository->get('4', 'tire_series');
        $tireSeriesOptionId = $tireSeriesAttribute->getSource()->getOptionId('Radial TA');

        $childConditions1 = $this->conditionInterfaceFactory->create()
            ->setConditionType(Subselect::class)
            ->setAttributeName('qty')
            ->setOperator('>=')
            ->setAggregatorType('all')
            ->setValue('4')
            ->setConditions([
                $this->conditionInterfaceFactory->create()
                    ->setConditionType(Product::class)
                    ->setAttributeName('coker_product_type')
                    ->setValue($typeOptionId)
                    ->setOperator('=='),
                $this->conditionInterfaceFactory->create()
                    ->setConditionType(Product::class)
                    ->setAttributeName('tire_series')
                    ->setValue($tireSeriesOptionId)
                    ->setOperator('=='),
            ]);

        $typeOptionId = $typeAttribute->getSource()->getOptionId('Wheel');
        $childConditions2 = $this->conditionInterfaceFactory->create()
            ->setConditionType(Subselect::class)
            ->setAttributeName('qty')
            ->setOperator('>=')
            ->setAggregatorType('all')
            ->setValue('4')
            ->setConditions([
                $this->conditionInterfaceFactory->create()
                    ->setConditionType(Product::class)
                    ->setAttributeName('coker_product_type')
                    ->setValue($typeOptionId)
                    ->setOperator('==')
            ]);
        $ruleCondition = $this->conditionInterfaceFactory->create()
            ->setConditionType(Combine::class)
            ->setAggregatorType('all')
            ->setValue('1')
            ->setConditions(
                [
                    $childConditions1,
                    $childConditions2
                ]
            );
        $newRule->setCondition($ruleCondition);

        try {
            $newRule = $this->ruleRepository->save($newRule);
            /** @var CouponInterface $couponCode */
            $couponCode = $this->couponInterfaceFactory->create();
            $couponCode->setRuleId($newRule->getRuleId())
                ->setCode('BFG10%OFF')
                ->setIsPrimary(true);
            $this->couponRepository->save($couponCode);

            return $newRule;
        } catch (\Exception $exception) {
            $this->logger->debug($exception);
        }
    }

    private function createCartRuleCleanerDressingFreeship()
    {
        $cokerWebsiteId = $this->storeManager->getWebsite(CokerStoreInterface::COKER_WEBSITE_CODE)->getId();
        $newRule = $this->ruleInterfaceFactory->create();
        $newRule->setName('Cleaner Dressing Free Shipping')
            ->setDescription('Cleaner Dressing Free Shipping')
            ->setIsAdvanced(1)
            ->setStopRulesProcessing(0)
            ->setCustomerGroupIds([0, 1, 2, 3])
            ->setWebsiteIds([$cokerWebsiteId])
            ->setIsRss(1)
            ->setUsesPerCustomer(999999)
            ->setDiscountStep(0)
            ->setDiscountQty(0)
            ->setCouponType(RuleInterface::COUPON_TYPE_NO_COUPON)
            ->setSimpleAction(RuleInterface::DISCOUNT_ACTION_BY_PERCENT)
            ->setDiscountAmount(0)
            ->setApplyToShipping(1)
            ->setSortOrder(1)
            ->setIsActive(1)
            ->setSimpleFreeShipping(1);

        $typeAttribute = $this->attributeRepository->get('4', 'coker_product_type');
        $typeOptionId = $typeAttribute->getSource()->getOptionId('Tire');

        $childConditions1 = $this->conditionInterfaceFactory->create()
            ->setConditionType(\Magento\SalesRule\Model\Rule\Condition\Address::class)
            ->setAttributeName('country_id')
            ->setOperator('==')
            ->setValue('US');
        $childConditions2 = $this->conditionInterfaceFactory->create()
            ->setConditionType(Found::class)
            ->setValue(1)
            ->setAggregatorType('all')
            ->setConditions([
                $this->conditionInterfaceFactory->create()
                    ->setConditionType(Product::class)
                    ->setAttributeName('coker_product_type')
                    ->setValue($typeOptionId)
                    ->setOperator('==')
            ]);
        $ruleCondition = $this->conditionInterfaceFactory->create()
            ->setConditionType(Combine::class)
            ->setAggregatorType('all')
            ->setValue('1')
            ->setConditions(
                [
                    $childConditions1,
                    $childConditions2
                ]
            );
        $newRule->setCondition($ruleCondition);

        $actionConditions = $this->conditionInterfaceFactory->create()
            ->setConditionType(Combine::class)
            ->setAggregatorType('any')
            ->setValue('1')
            ->setConditions(
                [
                    $this->conditionInterfaceFactory->create()
                        ->setConditionType(Product::class)
                        ->setAttributeName('sku')
                        ->setValue('91810')
                        ->setOperator('=='),
                    $this->conditionInterfaceFactory->create()
                        ->setConditionType(Product::class)
                        ->setAttributeName('sku')
                        ->setValue('CLEANDRESS')
                        ->setOperator('=='),
                    $this->conditionInterfaceFactory->create()
                        ->setConditionType(Product::class)
                        ->setAttributeName('sku')
                        ->setValue('91800')
                        ->setOperator('==')
                ]
            );
        $newRule->setActionCondition($actionConditions);

        try {
            $newRule = $this->ruleRepository->save($newRule);
            return $newRule;
        } catch (\Exception $exception) {
            $this->logger->debug($exception);
        }
    }
    private function createCartRuleAMCA10Discount()
    {
        $cokerWebsiteId = $this->storeManager->getWebsite(CokerStoreInterface::COKER_WEBSITE_CODE)->getId();
        $newRule = $this->ruleInterfaceFactory->create();
        $newRule->setName('AMCA 10% Discount')
            ->setDescription('AMCA 10% Discount')
            ->setIsAdvanced(1)
            ->setStopRulesProcessing(0)
            ->setCustomerGroupIds([0, 1, 2, 3])
            ->setWebsiteIds([$cokerWebsiteId])
            ->setIsRss(1)
            ->setUsesPerCoupon(0)
            ->setUsesPerCustomer(0)
            ->setDiscountStep(0)
            ->setDiscountQty(0)
            ->setCouponType(RuleInterface::COUPON_TYPE_SPECIFIC_COUPON)
            ->setUseAutoGeneration(1)
            ->setSimpleAction(RuleInterface::DISCOUNT_ACTION_BY_PERCENT)
            ->setDiscountAmount(10)
            ->setSortOrder(1)
            ->setIsActive(0)
            ->setSimpleFreeShipping(0);

        // Set rule condition
        $ruleCondition = $this->conditionInterfaceFactory->create()
            ->setConditionType(Combine::class)
            ->setAggregatorType('all')
            ->setValue('1')
            ->setConditions(
                [
                $this->conditionInterfaceFactory->create()
                    ->setConditionType(\Magento\SalesRule\Model\Rule\Condition\Address::class)
                    ->setAttributeName('country_id')
                    ->setOperator('==')
                    ->setValue('US')
                ]
            );
        $newRule->setCondition($ruleCondition);

        // Set action conditions
        $typeAttribute = $this->attributeRepository->get('4', 'coker_product_type');
        $typeOptionId = $typeAttribute->getSource()->getOptionId('Motorcycle Tire');
        $childConditions1 = $this->conditionInterfaceFactory->create()
            ->setConditionType(Combine::class)
            ->setAggregatorType('all')
            ->setValue('1')
            ->setConditions([
                $this->conditionInterfaceFactory->create()
                    ->setConditionType(Product::class)
                    ->setAttributeName('coker_product_type')
                    ->setValue($typeOptionId)
                    ->setOperator('=='),
                $this->conditionInterfaceFactory->create()
                    ->setConditionType(Product::class)
                    ->setAttributeName('quote_item_qty')
                    ->setValue(1)
                    ->setOperator('>='),
            ]);

        $typeOptionId = $typeAttribute->getSource()->getOptionId('Tube');
        $childConditions2 = $this->conditionInterfaceFactory->create()
            ->setConditionType(Combine::class)
            ->setAggregatorType('all')
            ->setValue('1')
            ->setConditions([
                $this->conditionInterfaceFactory->create()
                    ->setConditionType(Product::class)
                    ->setAttributeName('coker_product_type')
                    ->setValue($typeOptionId)
                    ->setOperator('=='),
                $this->conditionInterfaceFactory->create()
                    ->setConditionType(Product::class)
                    ->setAttributeName('quote_item_qty')
                    ->setValue(1)
                    ->setOperator('>='),
            ]);
        $ruleCondition = $this->conditionInterfaceFactory->create()
            ->setConditionType(Combine::class)
            ->setAggregatorType('any')
            ->setValue('1')
            ->setConditions(
                [
                    $childConditions1,
                    $childConditions2
                ]
            );
        $newRule->setActionCondition($ruleCondition);

        try {
            $newRule = $this->ruleRepository->save($newRule);
            return $newRule;
        } catch (\Exception $exception) {
            $this->logger->debug($exception);
        }
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [
            CreateCartPriceRules::class
        ];
    }
}
