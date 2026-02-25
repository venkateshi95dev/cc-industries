<?php
/**
 * Crimson Agility, LLC
 */
namespace Crimson\Category\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddC8Category implements DataPatchInterface
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory
     */
    protected $_urlRewriteCollectionFactory;

    /**
     * AddC8Category constructor.
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory $urlRewriteCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory $urlRewriteCollectionFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryCollectionFactory = $categoryFactory;
        $this->_urlRewriteCollectionFactory = $urlRewriteCollectionFactory;
    }

    public function apply()
    {
        /** @var \Magento\Catalog\Model\Category $c7Category */
        $c7Category = $this->categoryFactory->create();
        $c7Category->load(26428);

        /** @var \Magento\Catalog\Model\Category $c8Category */
        $c8Category = $this->categoryFactory->create();
        $c8Category->setParentId($c7Category->getParentId());
        $c8Category->setLevel($c7Category->getLevel());
        $c8Category->setName('20 C8 Corvette Parts');
        $c8Category->setDisplayMode($c7Category->getDisplayMode());
        $c8Category->setUrlKey('20-c8');
        $c8Category->setIsActive($c7Category->getIsActive());
        $c8Category->setPath($c7Category->getParentCategory()->getPath());
        $c8Category->setIncludeInMenu($c7Category->getIncludeInMenu());
        $c8Category->setData('is_anchor', $c7Category->getData('is_anchor'));
        $c8Category->setData('description', "<p>As with the release of the 7th generation Corvette, Zip will pave the way for C8 Corvette owners to enhance, personalize and protect their new Stingray.</p>");
        $c8Category->setData('meta_title', "C8 Corvette Parts");
        $c8Category->setData('meta_keywords', "2020");
        $c8Category->setData('meta_description', "Thousands of C8 Corvette Parts and Accessories to add horsepower, personalize your 14-19 C7 Corvette and get it running, sounding and looking great. Since 1977 - Corvettes are all we do.");
        $c8Category->save();

        foreach ($c7Category->getChildrenCategories() as $category) {

            /** @var \Magento\Catalog\Model\Category $c8CategorySubCategory */
            $c8CategorySubCategory = $this->categoryFactory->create();

            $c8CategorySubCategory->setParentId($c8Category->getParentId());
            $c8CategorySubCategory->setPosition($category->getPosition());
            $c8CategorySubCategory->setLevel($category->getLevel());
            $c8CategorySubCategory->setName($category->getName());
            $c8CategorySubCategory->setDisplayMode($category->getDisplayMode());
            $c8CategorySubCategory->setUrlKey($category->getUrlKey());
            $c8CategorySubCategory->setIsActive($category->getIsActive());
            $c8CategorySubCategory->setPath($c8Category->getPath());
            $c8CategorySubCategory->setIncludeInMenu($category->getIncludeInMenu());
            $c8CategorySubCategory->setData('is_anchor', $category->getData('is_anchor'));
            $c8CategorySubCategory->save();

            /** @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection $rewriteCollection */
            $rewriteCollection = $this->_urlRewriteCollectionFactory->create();
            $rewriteCollection->addFieldToFilter('entity_id', $c8CategorySubCategory->getId())
                ->addFieldToFilter('entity_type', 'category');

            if ($rewriteCollection->count()) {
                $rewriteCollection->getFirstItem()->setData('request_path', '20-c8/'.$c8CategorySubCategory->getUrlKey().'.html')->save();
            }

            foreach ($category->getChildrenCategories() as $child) {

                /** @var \Magento\Catalog\Model\Category $c8CategorySubCategory */
                $c8CategorySubCategoryChild = $this->categoryFactory->create();

                $c8CategorySubCategoryChild->setParentId($c8CategorySubCategory->getParentId());
                $c8CategorySubCategoryChild->setPosition($child->getPosition());
                $c8CategorySubCategoryChild->setLevel($child->getLevel());
                $c8CategorySubCategoryChild->setName($child->getName());
                $c8CategorySubCategoryChild->setDisplayMode($child->getDisplayMode());
                $c8CategorySubCategoryChild->setUrlKey($child->getUrlKey());
                $c8CategorySubCategoryChild->setIsActive($child->getIsActive());
                $c8CategorySubCategoryChild->setPath($c8CategorySubCategory->getPath());
                $c8CategorySubCategoryChild->setIncludeInMenu($child->getIncludeInMenu());
                $c8CategorySubCategoryChild->setData('is_anchor', $child->getData('is_anchor'));
                $c8CategorySubCategoryChild->save();

                /** @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection $rewriteCollection */
                $rewriteCollection = $this->_urlRewriteCollectionFactory->create();
                $rewriteCollection->addFieldToFilter('entity_id', $c8CategorySubCategoryChild->getId())
                    ->addFieldToFilter('entity_type', 'category');

                if ($rewriteCollection->count()) {
                    $rewriteCollection->getFirstItem()->setData('request_path', '20-c8/'.$c8CategorySubCategory->getUrlKey().'/'.$c8CategorySubCategoryChild->getUrlKey().'.html')->save();
                }
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
}