<?php

namespace Crimson\Testimonial\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface TestimonialInterface extends ExtensibleDataInterface
{
    const TESTIMONIAL_ID = 'testimonial_id';
    const NICK_NAME = 'nick_name';
    const EMAIL = 'email';
    const IMAGE = 'image';
    const COMPANY_ADDRESS = 'company_address';
    const COMPANY_NAME = 'company_name';
    const COMPANY_WEBSITE = 'company_website';
    const POSITION = 'position';
    const LINKEDIN = 'linkedin';
    const FACEBOOK = 'facebook';
    const TWITTER = 'twitter';
    const YOUTUBE = 'youtube';
    const VIMEO = 'vimeo';
    const GOOGLEPLUS = 'googleplus';
    const ADDRESS = 'address';
    const TESTIMONIAL = 'testimonial';
    const CREATE_TIME = 'create_time';
    const RATING = 'rating';
    const JOB = 'job';
    const IS_ACTIVE = 'is_active';
    const TITLE = 'title';
    const STORES = 'stores';
    const CATEGORIES = 'categories';
    const PRODUCTS = 'products';

    /**
     * Get value
     * @return int|null
     */
    public function getTestimonialId();

    /**
     * Set value
     * @param int|null $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setTestimonialId($value);

    /**
     * Get value
     * @return string
     */
    public function getNickName();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setNickName($value);

    /**
     * Get value
     * @return string
     */
    public function getEmail();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setEmail($value);

    /**
     * Get value
     * @return string
     */
    public function getImage();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setImage($value);

    /**
     * Get value
     * @return string
     */
    public function getCompanyAddress();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setCompanyAddress($value);

    /**
     * Get value
     * @return string
     */
    public function getCompanyName();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setCompanyName($value);

    /**
     * Get value
     * @return string
     */
    public function getCompanyWebsite();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setCompanyWebsite($value);

    /**
     * Get value
     * @return int|null
     */
    public function getPosition();

    /**
     * Set value
     * @param int|null $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setPosition($value);

    /**
     * Get value
     * @return string|null
     */
    public function getLinkedin();

    /**
     * Set value
     * @param string|null $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setLinkedin($value);

    /**
     * Get value
     * @return string|null
     */
    public function getFacebook();

    /**
     * Set value
     * @param string|null $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setFacebook($value);

    /**
     * Get value
     * @return string|null
     */
    public function getTwitter();

    /**
     * Set value
     * @param string|null $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setTwitter($value);

    /**
     * Get value
     * @return string|null
     */
    public function getYoutube();

    /**
     * Set value
     * @param string|null $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setYoutube($value);

    /**
     * Get value
     * @return string|null
     */
    public function getVimeo();

    /**
     * Set value
     * @param string|null $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setVimeo($value);

    /**
     * Get value
     * @return string|null
     */
    public function getGoogleplus();

    /**
     * Set value
     * @param string|null $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setGoogleplus($value);

    /**
     * Get value
     * @return string
     */
    public function getAddress();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setAddress($value);

    /**
     * Get value
     * @return string
     */
    public function getTestimonial();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setTestimonial($value);


    /**
     * Get value
     * @return string
     */
    public function getCreateTime();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setCreateTime($value);

    /**
     * Get value
     * @return int
     */
    public function getIsActive();

    /**
     * Set value
     * @param int $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setIsActive($value);

    /**
     * Get value
     * @return int|null
     */
    public function getRating();

    /**
     * Set value
     * @param int|null $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setRating($value);

    /**
     * Get value
     * @return string
     */
    public function getJob();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setJob($value);

    /**
     * Get value
     * @return string
     */
    public function getTitle();

    /**
     * Set value
     * @param string $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setTitle($value);

    /**
     * Get value
     * @return int[]
     */
    public function getStores();

    /**
     * Set value
     * @param int[] $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setStores($value);

    /**
     * Get value
     * @return string[]|int[]
     */
    public function getCategories();

    /**
     * Set value
     * @param string[]|int[] $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setCategories($value);

    /**
     * Get value
     * @return string[]|int[]
     */
    public function getProducts();

    /**
     * Set value
     * @param string[]|int[] $value
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface
     */
    public function setProducts($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Crimson\Testimonial\Api\Data\TestimonialExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Crimson\Testimonial\Api\Data\TestimonialExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Crimson\Testimonial\Api\Data\TestimonialExtensionInterface $extensionAttributes
    );
}
