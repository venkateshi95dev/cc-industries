<?php

namespace Crimson\Testimonial\Model;
use Crimson\Testimonial\Api\Data\TestimonialExtensionInterface;
use Crimson\Testimonial\Api\Data\TestimonialInterface;
use Crimson\Testimonial\Api\Data\TestimonialInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\AbstractModel;

class Testimonial extends AbstractModel implements TestimonialInterface
{
    /**
     * Blog's Statuses
     */
    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED = 0;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     *
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * URL Model instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_testimonialHelper;

    protected $_resource;
    /**
     * Page cache tag
     */
    const CACHE_TAG = 'ves_testimonial_testimonial';

    protected $testimonialDataFactory;

    protected $dataObjectHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Crimson\Testimonial\Model\ResourceModel\Testimonial $resource = null,
        \Crimson\Testimonial\Model\ResourceModel\Testimonial\Collection $resourceCollection = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $url,
        \Crimson\Testimonial\Helper\Data $testimonialHelper,
        TestimonialInterfaceFactory $testimonialDataFactory,
        DataObjectHelper $dataObjectHelper,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_url          = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_resource          = $resource;
        $this->_testimonialHelper = $testimonialHelper;
        $this->testimonialDataFactory = $testimonialDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Crimson\Testimonial\Model\ResourceModel\Testimonial');
    }

    /**
     * Retrieve category model with category data
     * @return TestimonialInterface
     */
    public function getDataModel()
    {
        $testimonialData = $this->getData();

        $testimonialDataObject = $this->testimonialDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $testimonialDataObject,
            $testimonialData,
            TestimonialInterface::class
        );

        return $testimonialDataObject;
    }
    /**
     * Prevent blocks recursion
     *
     * @return AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $needle = 'testimonial_id="'.$this->getId().'"';
        if (false == strstr($this->getContent(), $needle)) {
            return parent::beforeSave();
        }

        throw new \Magento\Framework\Exception\LocalizedException(
            __('Make sure that category content does not reference the block itself.')
        );
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
    public function getTestimonialId(){
        return $this->getData(self::TESTIMONIAL_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setTestimonialId($value){
        return $this->setData(self::TESTIMONIAL_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getNickName(){
        return $this->getData(self::NICK_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setNickName($value){
        return $this->setData(self::NICK_NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail(){
        return $this->getData(self::EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($value){
        return $this->setData(self::EMAIL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage(){
        return $this->getData(self::IMAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setImage($value){
        return $this->setData(self::IMAGE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompanyAddress(){
        return $this->getData(self::COMPANY_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setCompanyAddress($value){
        return $this->setData(self::COMPANY_ADDRESS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompanyName(){
        return $this->getData(self::COMPANY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCompanyName($value){
        return $this->setData(self::COMPANY_NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompanyWebsite(){
        return $this->getData(self::COMPANY_WEBSITE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCompanyWebsite($value){
        return $this->setData(self::COMPANY_WEBSITE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition(){
        return $this->getData(self::POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($value){
        return $this->setData(self::POSITION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkedin(){
        return $this->getData(self::LINKEDIN);
    }

    /**
     * {@inheritdoc}
     */
    public function setLinkedin($value){
        return $this->setData(self::LINKEDIN, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFacebook(){
        return $this->getData(self::FACEBOOK);
    }

    /**
     * {@inheritdoc}
     */
    public function setFacebook($value){
        return $this->setData(self::FACEBOOK, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getTwitter(){
        return $this->getData(self::TWITTER);
    }

    /**
     * {@inheritdoc}
     */
    public function setTwitter($value){
        return $this->setData(self::TWITTER, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getYoutube(){
        return $this->getData(self::YOUTUBE);
    }

    /**
     * {@inheritdoc}
     */
    public function setYoutube($value){
        return $this->setData(self::YOUTUBE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getVimeo(){
        return $this->getData(self::VIMEO);
    }

    /**
     * {@inheritdoc}
     */
    public function setVimeo($value){
        return $this->setData(self::VIMEO, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getGoogleplus(){
        return $this->getData(self::GOOGLEPLUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setGoogleplus($value){
        return $this->setData(self::GOOGLEPLUS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress(){
        return $this->getData(self::ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddress($value){
        return $this->setData(self::ADDRESS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getTestimonial(){
        return $this->getData(self::TESTIMONIAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setTestimonial($value){
        return $this->setData(self::TESTIMONIAL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateTime()
    {
        $dateTime   = $this->getData(self::CREATE_TIME);
        $dateFormat = $this->_testimonialHelper->getConfig('general/dateformat');
        return $this->_testimonialHelper->getFormatDate($dateTime, $dateFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreateTime($value){
        return $this->setData(self::CREATE_TIME, $value);
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
    public function getRating(){
        return $this->getData(self::RATING);
    }

    /**
     * {@inheritdoc}
     */
    public function setRating($value){
        return $this->setData(self::RATING, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getJob(){
        return $this->getData(self::JOB);
    }

    /**
     * {@inheritdoc}
     */
    public function setJob($value){
        return $this->setData(self::JOB, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(){
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($value){
        return $this->setData(self::TITLE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData(self::STORES) : $this->getData('store_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setStores($value){
        return $this->setData(self::STORES, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategories(){
        return $this->getData(self::CATEGORIES);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategories($value){
        return $this->setData(self::CATEGORIES, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts(){
        return $this->getData(self::PRODUCTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setProducts($value){
        return $this->setData(self::PRODUCTS, $value);
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
        \Crimson\Testimonial\Api\Data\TestimonialExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
