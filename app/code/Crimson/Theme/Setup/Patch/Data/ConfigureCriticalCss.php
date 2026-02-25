<?php

namespace Crimson\Theme\Setup\Patch\Data;

use Crimson\CatalogCriticalCss\Setup\Patch\Data\AddCriticalCssGroupCategoryAttribute;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Zend_Db_Expr;

class ConfigureCriticalCss implements DataPatchInterface
{
    const TOP_LEVEL_BRANCH_CATEGORY_ENTITY_IDS = [
        26417,
        26418,
        26419,
        26420,
        26421,
        26422,
        26428,
        31217,
        26423,
        26424,
    ];

    private WriterInterface $configWriter;

    private CategoryResource $categoryResource;

    public function __construct(
        WriterInterface $configWriter,
        CategoryResource $categoryResource
    ) {
        $this->configWriter = $configWriter;
        $this->categoryResource = $categoryResource;
    }

    public function apply()
    {
        $this->configWriter->save('system/critical_css/force_include_selectors', ".toolbar-products .pages\n.section-item-content .menu-container .menu-mobile");
        $this->configWriter->save('system/critical_css/generate_timeout_ms', 45000);
        $this->configWriter->save('system/critical_css/generate_render_wait_time_ms', 15000);

        $this->setBranchCategoriesCriticalCssGroup();
    }

    public function setBranchCategoriesCriticalCssGroup()
    {
        $criticalCssGroupAttribute = $this->categoryResource->getAttribute('critical_css_group');

        $connection = $this->categoryResource->getConnection();

        $select = $connection->select();
        $select->from(
            ['category' => $connection->getTableName('catalog_category_entity')],
            []
        );
        $condition = [];
        foreach (self::TOP_LEVEL_BRANCH_CATEGORY_ENTITY_IDS as $topLevelBranchCategoryEntityId) {
            $condition[] = 'path = "1/2/' . $topLevelBranchCategoryEntityId . '"';
            $condition[] = 'path like "1/2/' . $topLevelBranchCategoryEntityId . '/%"';
        }
        $select->where("(" . implode(") OR (", $condition) . ")");
        // Only select categories with children (only select categories that are referenced as being a parent by at least one child)
        $select->where("entity_id NOT IN (SELECT parent_id FROM catalog_category_entity pcategory)");
        $select->columns([
            'value_id' => new Zend_Db_Expr('NULL'), 
            'attribute_id' => new Zend_Db_Expr($criticalCssGroupAttribute->getAttributeId()),
            'store_id' => new Zend_Db_Expr('0'),
            'row_id',
            'value' => new Zend_Db_Expr('"branch_category"'),
        ]);
        
        $insertQuery = $connection->insertFromSelect(
            $select,
            $criticalCssGroupAttribute->getBackendTable()
        );

        $connection->query($insertQuery);

    }

    public static function getDependencies()
    {
        return [
            AddCriticalCssGroupCategoryAttribute::class
        ];
    }

    public function getAliases()
    {
        return [];
    }
}
