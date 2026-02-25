<?php

namespace Crimson\Theme\Setup\Patch;

use Crimson\EavCriticalCss\Api\AttributeCriticalCssSettingsRepositoryInterface;
use Crimson\EavCriticalCss\Model\AttributeCriticalCssSettingsRegistry;
use Crimson\EavCriticalCss\Model\Data\AttributeCriticalCssSettingsFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

abstract class AbstractAttributeCriticalCssSettingsDataPatch implements DataPatchInterface
{
    /** @var AttributeCriticalCssSettingsRegistry */
    private $attributeCriticalCssSettingsRegistry;

    /** @var AttributeCriticalCssSettingsRepositoryInterface */
    private $attributeCriticalCssSettingsRepository;

    /** @var AttributeCriticalCssSettingsFactory */
    private $attributeCriticalCssSettingsFactory;

    /** @var EavConfig */
    private $eavConfig;

    public function __construct(
        AttributeCriticalCssSettingsRegistry $attributeCriticalCssSettingsRegistry,
        AttributeCriticalCssSettingsRepositoryInterface $attributeCriticalCssSettingsRepository,
        AttributeCriticalCssSettingsFactory $attributeCriticalCssSettingsFactory,
        EavConfig $eavConfig
    ) {
        $this->attributeCriticalCssSettingsRegistry = $attributeCriticalCssSettingsRegistry;
        $this->attributeCriticalCssSettingsRepository = $attributeCriticalCssSettingsRepository;
        $this->attributeCriticalCssSettingsFactory = $attributeCriticalCssSettingsFactory;
        $this->eavConfig = $eavConfig;
    }

    protected function updateCriticalCssSettings(string $entityTypeCode, string $attributeCode, string $designRelation, null|array|string $criticalValues = null): void
    {
        try {
            $criticalCssSettings = $this->attributeCriticalCssSettingsRegistry->getByCode($entityTypeCode, $attributeCode);
        } catch (NoSuchEntityException $e) {
            $attribute = $this->eavConfig->getAttribute($entityTypeCode, $attributeCode);

            $criticalCssSettings = $this->attributeCriticalCssSettingsFactory->create();
            $criticalCssSettings->setAttributeId($attribute->getId());
        }
        $criticalCssSettings->setDesignRelation($designRelation);
        $criticalCssSettings->setCriticalValues($criticalValues);
        $this->attributeCriticalCssSettingsRepository->save($criticalCssSettings);
    }

    public function getAliases() 
    { 
        return [];
    }

    public static function getDependencies() 
    { 
        return [];
    }
}