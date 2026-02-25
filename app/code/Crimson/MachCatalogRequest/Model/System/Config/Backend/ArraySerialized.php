<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/15/2019 11:03 AM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Model\System\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Backend for serialized array data
 */
class ArraySerialized extends Value implements ProcessorInterface
{
    /**
     * @var Json
     */
    protected $serializer;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Json $serializer,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->serializer = $serializer;
    }

    /**
     * @return ArraySerialized|void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if (!is_array($value)) {
            $value = $this->serializer->unserialize($value);
        }
        unset($value['__empty']);
        $this->setValue($value);
    }

    /**
     * Prepare data before save
     */
    public function beforeSave(): ArraySerialized
    {
        if (is_array($this->getValue())) {
            $value = $this->serializer->serialize($this->getValue());
            $this->setValue($value);
        }
        return parent::beforeSave();
    }

    /**
     * Process config value
     *
     * @param string $value Raw value of the configuration field
     *
     * @return string Processed value
     */
    public function processValue($value)
    {
        if (empty($value)) {
            $value = [];
        }
        if (!is_array($value)) {
            $value = $this->serializer->unserialize($value);
        }

        unset($value['__empty']);

        return $value;
    }

    /**
     * @return mixed|string
     */
    public function getOldValue()
    {
        /**
         * Change icoast@crimsonagility.com on 4/11/2017 at 5:10 PM
         * Description: Removed (string) cast
         */
        return $this->_config->getValue(
            $this->getPath(),
            $this->getScope() ?: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $this->getScopeCode()
        );
    }
}
