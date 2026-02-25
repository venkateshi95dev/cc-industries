<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Model\AttributeSetRepository;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;

final class CreateAttributeSetsForCC implements DataPatchInterface
{
    const ATTR_GROUP_NAME = 'Corvette Centre';
    public function __construct(
        private readonly CategorySetupFactory $categorySetupFactory,
        private readonly AttributeSetFactory $attributeSetFactory,
        private readonly AttributeSetRepository $attributeSetRepository,
        private readonly AttributeGroupRepositoryInterface $attributeGroupRepository,
        private readonly AttributeGroupInterfaceFactory $groupFactory
    ) {}

    public function apply(): void
    {
        // Path to the JSON file
        $jsonFilePath = BP . '/app/code/Crimson/CorvetteCentral/Setup/data/product_attribute_sets.json';
        // Parse JSON file
        if (!file_exists($jsonFilePath)) {
            throw new \Exception("JSON file not found: " . $jsonFilePath);
        }

        $categorySetup = $this->categorySetupFactory->create();
        $jsonData = file_get_contents($jsonFilePath);
        $attributeSetsData = json_decode($jsonData, true);

        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $defaultAttributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        foreach ($attributeSetsData['attribute_sets'] as $set) {
            $attributeSet = $this->attributeSetFactory->create();
            $data = [
                'attribute_set_name' => $set['set_name'],
                'entity_type_id' => $entityTypeId,
                'sort_order' => 200,
            ];
            $attributeSet->setData($data);
            $attributeSet->validate();
            $attributeSet->initFromSkeleton($defaultAttributeSetId);
            $attributeSet->save();

            $attributeGroup = $this->groupFactory->create();
            $attributeGroup->setAttributeSetId($attributeSet->getAttributeSetId());
            $attributeGroup->setAttributeGroupName(self::ATTR_GROUP_NAME);
            $attributeGroup->setSortOrder(100); // Adjust the sort order if needed
            $attributeGroup = $this->attributeGroupRepository->save($attributeGroup);
            // Assign Attributes to Group
            foreach ($set['attributes'] as $attributeCode) {
                try {
                    $categorySetup->addAttributeToSet(
                        $entityTypeId,
                        $attributeSet->getId(),
                        $attributeGroup->getAttributeGroupId(),
                        $attributeCode
                    );
                } catch (\Exception $e) {
                    error_log("Failed to assign attribute {$attributeCode}: " . $e->getMessage());
                }
            }
        }
    }

    public static function getDependencies(): array
    {
        return [
            CreateProductAttributesForCC::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
