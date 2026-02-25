<?php

namespace Crimson\Testimonial\Model;
use Crimson\Testimonial\Api\Data\CategoryInterface;
use Crimson\Testimonial\Api\Data\CategoryInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\AbstractModel;

class Category extends AbstractModel implements CategoryInterface
{
    /**
     * Testimonial's Statuses
     */
    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED = 0;

    protected $_resource;

    protected $categoryDataFactory;

    protected $dataObjectHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Crimson\Testimonial\Model\ResourceModel\Category $resource = null,
        \Crimson\Testimonial\Model\ResourceModel\Category\Collection $resourceCollection = null,
        CategoryInterfaceFactory $categoryDataFactory,
        DataObjectHelper $dataObjectHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_resource = $resource;
        $this->categoryDataFactory = $categoryDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Crimson\Testimonial\Model\ResourceModel\Category');
    }

    /**
     * Retrieve category model with category data
     * @return CategoryInterface
     */
    public function getDataModel()
    {
        $categoryData = $this->getData();

        $categoryDataObject = $this->categoryDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $categoryDataObject,
            $categoryData,
            CategoryInterface::class
        );

        return $categoryDataObject;
    }

    /**
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
                self::STATUS_ENABLED  => __('Enabled'),
                self::STATUS_DISABLED => __('Disabled'),
               ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryId(){
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryId($value){
        return $this->setData(self::CATEGORY_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(){
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($value){
        return $this->setData(self::NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreationTime(){
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreationTime($value){
        return $this->setData(self::CREATION_TIME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive(){
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($value){
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->getDataExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Crimson\Testimonial\Api\Data\CategoryExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
