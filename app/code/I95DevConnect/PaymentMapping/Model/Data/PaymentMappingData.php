<?php

/**
 * @author Arushi Bansal
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\PaymentMapping\Model\Data;

use I95DevConnect\PaymentMapping\Api\Data\PaymentMappingDataInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * i95Dev Payment mapping data model
 */
class PaymentMappingData extends AbstractModel implements PaymentMappingDataInterface
{
    // @codingStandardsIgnoreStart
    protected $_eventPrefix = 'i95dev_payment_mapping';
    // @codingStandardsIgnoreEnd

    /**
     * Construct method
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init('I95DevConnect\PaymentMapping\Model\ResourceModel\PaymentMapping');
    }// @codingStandardsIgnoreEnd

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get mapping data
     *
     * @return string|null
     */
    public function getMappedData()
    {
        return $this->getData(self::MAPPED_DATA);
    }

    /**
     * Set Payment Mapping Data
     *
     * @param string $mappedData
     * @return $this
     */
    public function setMappedData($mappedData)
    {
        return $this->setData(self::MAPPED_DATA, $mappedData);
    }

    /**
     * Get Created Date
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created date
     *
     * @param string $createdDate
     * @return $this
     */
    public function setCreatedAt($createdDate)
    {
        return $this->setData(self::CREATED_AT, $createdDate);
    }
}
