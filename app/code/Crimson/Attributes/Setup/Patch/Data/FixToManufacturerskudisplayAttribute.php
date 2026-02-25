<?php
/**
 * @namespace   Crimson
 * @module      Attributes
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        07/18/2019
 */
namespace Crimson\Attributes\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class FixToManufacturerskudisplayAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory     $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->resource = $resource;
    }


    public function apply()
    {
        // Update attribute backend type
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $productEntityId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $categorySetup->updateAttribute($productEntityId, 'manufacturerskudisplay', 'backend_type', 'varchar');

        // Get attribute ID
        $attributeId = $categorySetup->getAttributeId($productEntityId, 'manufacturerskudisplay');

        // Move data from catalog_product_entity_text to catalog_product_entity_varchar
        /**
         * @var $resource \Magento\Framework\App\ResourceConnection
         */
        $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

        $textTable = $connection->getTablename('catalog_product_entity_text');
        $varcharTable = $connection->getTablename('catalog_product_entity_varchar');

        $fieldsToCopy = array_keys($connection->describeTable($textTable));
        $fieldsToCopy = array_combine($fieldsToCopy, $fieldsToCopy);
        $fieldsToCopy['value_id'] = new \Zend_Db_Expr('NULL');

        $select = $connection->select()->from($textTable, $fieldsToCopy)->where('attribute_id = ?', $attributeId);
        $insertQuery = $connection->insertFromSelect($select, $varcharTable);

        $connection->query($insertQuery);

        // Remove data from catalog_product_entity_text
        $deleteQuery = $connection->deleteFromSelect($select, $textTable);

        $connection->query($deleteQuery);
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [
            \Crimson\Attributes\Setup\Patch\Data\AddProductAttributes::class
        ];
    }
}