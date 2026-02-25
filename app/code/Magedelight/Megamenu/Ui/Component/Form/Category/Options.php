<?php
namespace Magedelight\Megamenu\Ui\Component\Form\Category;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as      CategoryCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magedelight\Megamenu\Api\LabelRepositoryInterface;
use Magento\Framework\Json\DecoderInterface;

/**
* Options tree for "Categories" field
*/
class Options implements OptionSourceInterface
{

    protected $categoryCollectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $categoryTree;

     /**
     * @inheritDoc
     */
    protected $labelRepository;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        RequestInterface $request,
        \Magento\Framework\Registry $coreRegistry,
        LabelRepositoryInterface $labelRepository,
        DecoderInterface $jsonDecoder

    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->request = $request;
        $this->coreRegistry = $coreRegistry;
        $this->jsonDecoder = $jsonDecoder;
        $this->labelRepository = $labelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getCategoryTree();
    }

    /**
     * Retrieve categories tree
     *
     * @return array
     */
    protected function getCategoryTree()
    {
        if ($this->categoryTree === null) {

            $collection = $this->getCategoryCollection();
            $categoryIds = $this->getToBeAssignedcategory();

            if (count($categoryIds)) {
                $collection->addFieldToFilter('entity_id', ['in' => $categoryIds]);
            }
            $collection->addAttributeToSelect('name');
            foreach ($collection as $category) {
                $categoryId = $category->getEntityId();
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = [
                        'value' => $categoryId
                    ];
                }
                $categoryById[$categoryId]['label'] = $category->getName();
            }
            $this->categoryTree = $categoryById;
        }
        return $this->categoryTree;
    }

    /**
     * Retrieve category collection.
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategoryCollection()
    {
        $collection = $this->categoryCollectionFactory->create()->setStoreId(0);
        return $collection;
    }

    /**
     * Retrieve collection of category to be assigned.
     * @return array
     */
    public function getToBeAssignedcategory()
    {
        $collection = $this->getCategoryCollection();
        $collection->addAttributeToFilter('md_label',array('null' => true));

        $categoryIds = $collection->getColumnValues("entity_id");

        if(is_array($this->_getSelectedCategory())){
            $categoryIds = array_merge($categoryIds, $this->_getSelectedCategory());
        }
        
        return $categoryIds;
    }

    /**
     * @return array
     */
    protected function _getSelectedCategory()
    {
        $category = null;
        try {
            $label_id = $this->request->getParam('label_id');
            $labelCollection = $this->labelRepository->get($label_id);

            if(!empty($labelCollection->getData('category_assign'))){
                $categoryAssign = explode(',', $labelCollection->getData('category_assign'));
                $category = $categoryAssign;
            }
            
            if ($category === null) {
                $category = $this->getProductAssignedModel()->getCategoryAssign();
                if ($category) {
                    $category = $this->jsonDecoder->decode($category);
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
            $category = [];
        }
        
        return $category;
    }

     /**
     * @return array|null
     */
    public function getProductAssignedModel()
    {
        return $this->coreRegistry->registry('magedelight_megamenu_label');
    }
}