<?php

namespace Crimson\Testimonial\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface CategoryInterface extends ExtensibleDataInterface
{
    const CATEGORY_ID = 'category_id';
    const NAME = 'name';
    const CREATION_TIME = 'creation_time';
    const IS_ACTIVE = 'is_active';

    /**
     * Get value
     * @return int|null
     */
    public function getCategoryId();

    /**
     * Set value
     * @param int|null $value
     * @return \Crimson\Testimonial\Api\Data\CategoryInterface
     */
    public function setCategoryId($value);

    /**
     * Get value
     * @return string
     */
    public function getName();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\CategoryInterface
     */
    public function setName($value);

    /**
     * Get value
     * @return string
     */
    public function getCreationTime();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\CategoryInterface
     */
    public function setCreationTime($value);

    /**
     * Get value
     * @return int
     */
    public function getIsActive();

    /**
     * Set value
     * @param int $value
     * @return \Crimson\Testimonial\Api\Data\CategoryInterface
     */
    public function setIsActive($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Crimson\Testimonial\Api\Data\CategoryExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Crimson\Testimonial\Api\Data\CategoryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Crimson\Testimonial\Api\Data\CategoryExtensionInterface $extensionAttributes
    );
}
