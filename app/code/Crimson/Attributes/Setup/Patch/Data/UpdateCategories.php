<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        4/17/2019 8:22 AM
 * @brief
 */

namespace Crimson\Attributes\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpdateCategories implements
    DataPatchInterface,
    PatchVersionInterface
{

    protected $_categoryModelFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $_repository;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryModelFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $repository
    )
    {
        $this->_categoryModelFactory = $categoryModelFactory;
        $this->_repository = $repository;
    }

    public function apply()
    {
        $categoryCollection = $this->_categoryModelFactory->create()->addAttributeToSelect('*')->setStore(0);

        foreach($categoryCollection as $category) {
            $subCollection = $category->getCategories($category->getId());
            if ($subCollection->count() > 0 && $category->getIsAnchor()) {

                $category->setIsAnchor(0);
                $category->save();
                $this->_repository->save($category);

            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.1';
    }
}