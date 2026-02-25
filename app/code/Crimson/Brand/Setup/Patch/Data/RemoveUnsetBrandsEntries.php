<?php

namespace Crimson\Brand\Setup\Patch\Data;

use Magento\Framework\App\Area;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class RemoveUnsetBrandsEntries implements DataPatchInterface
{
    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var \Magento\Catalog\Model\Product\Action */
    private $productAction;

    /** @var \Magento\Catalog\Model\ResourceModel\Product */
    private $productResource;

    /** @var \Magento\Framework\App\State */
    private $appState;

    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Framework\App\State $appState
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->productAction = $productAction;
        $this->productResource = $productResource;
        $this->appState = $appState;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, function () {
            $productIdsByStore = $this->getUnsetBrandProductIdsByStore();
            foreach ($productIdsByStore as $storeId => $productIds) {
                $this->productAction->updateAttributes($productIds, ['brands' => null], $storeId);
            }
        });

        $this->moduleDataSetup->endSetup();
    }

    public function getUnsetBrandProductIdsByStore(): array
    {
        $brandAttribute = $this->productResource->getAttribute('brands');
        
        $connection = $this->productResource->getConnection();
        $select = $connection->select();
        $select->from(
            ['brands_attr' => $brandAttribute->getBackendTable()],
            ['store_id']
        );
        $select->joinInner(
            ['product' => $this->productResource->getEntityTable()],
            'product.' . $this->productResource->getLinkField() . ' = brands_attr.row_id',
            ['entity_id']
        );
        $select->where('brands_attr.attribute_id = ?', $brandAttribute->getAttributeId());
        $select->where('brands_attr.value = 0');
        $results = $connection->fetchAll($select);

        $productIdsByStore = [];
        foreach ($results as $result) {
            if (!isset($productIdsByStore[$result['store_id']])) {
                $productIdsByStore[$result['store_id']] = [];
            }
            $productIdsByStore[$result['store_id']][] = $result['entity_id'];
        }
        return $productIdsByStore;
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [
            AddProductBrandAttribute::class
        ];
    }
}
