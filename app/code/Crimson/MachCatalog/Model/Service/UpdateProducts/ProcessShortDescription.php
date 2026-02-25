<?php
/**
 * @namespace   Crimson
 * @module      MachCataloc
 * @author      Ryan Simmons
 * @email       rsimmons@crimsonagility.com
 * @date        7/17/2019 2:58 PM
 * @brief       Set the short description equal to the first 45 words of the description for all Mach-updated products
 */

namespace Crimson\MachCatalog\Model\Service\UpdateProducts;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Psr\Log\LoggerInterface;

/**
 * Class ProcessShortDescription
 * @package Crimson\MachCatalog\Model\Service\UpdateProducts
 */
class ProcessShortDescription implements ProcessorInterface
{
    const NUMBER_OF_WORDS_IN_SHORT_DESCRIPTION = 45;

    public function __construct(
        protected Product $productResource,
        protected LoggerInterface $logger
    ) {}

    public function process(ProductInterface $product): void
    {
	    $this->logger->debug(__('Analyzing product - Entity Id: %1', $product->getId()));

        try {
            $description = $product->getDescription();
            $updatedShortDescription = null;
            if (!empty($description)) {
                $wordsForShortDescription = array_slice(
                    explode(' ', $description), 0, self::NUMBER_OF_WORDS_IN_SHORT_DESCRIPTION
                );
                $updatedShortDescription = implode(' ', $wordsForShortDescription);
            }

	        $attribute = $this->productResource->getAttribute('short_description');
	        if ($attribute && $attribute->getId()) {
                $this->_updateAttribute($product->getRowId(), $attribute->getId(), $updatedShortDescription);
            }
        } catch (\Exception $e) {
	        $this->logger->debug(
		        __('Skipping product Entity Id: %1.  Error: %2' . $product->getId(), $e->getMessage())
	        );
        }
    }

    private function _updateAttribute($productId, $attrId, $attrValue): void
    {
        $connection = $this->productResource->getConnection();
        $mainTable = $connection->getTableName('catalog_product_entity_text');
        //check if row exists
        $select =
            $connection->select()
                ->from(['c_p_e_t' => $mainTable])
                ->where('c_p_e_t.row_id = ?', $productId)
                ->where('c_p_e_t.attribute_id = ?', $attrId)
                ->where('c_p_e_t.store_id = 0')
                ->reset(\Zend_Db_Select::COLUMNS)
                ->columns(['c_p_e_t.value_id']);

        $valueId = $connection->fetchOne($select);

        if ($valueId && !$attrValue) {
            $connection->delete(
                $mainTable,
                $connection->quoteInto('value_id = ?', $valueId)
            );
        } elseif ($valueId) {
            $connection->update(
                $mainTable,
                [
                    'value' => $attrValue
                ],
                [
                    'value_id = ?' => (int) $valueId,
                ]
            );
        } else {
            $data = [
                'row_id'       => $productId,
                'attribute_id' => $attrId,
                'store_id'     => 0,
                'value'        => $attrValue,
            ];

            $connection->insertMultiple(
                $mainTable,
                $data
            );
        }
    }
}
