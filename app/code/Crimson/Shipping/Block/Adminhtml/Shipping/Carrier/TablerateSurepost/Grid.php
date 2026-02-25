<?php
/**
 * @namespace   Crimson
 * @module      Shipping
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/25/2019
 */
namespace Crimson\Shipping\Block\Adminhtml\Shipping\Carrier\TablerateSurepost;

use Crimson\Shipping\Model\Carrier\Tableratesurepost;
use Crimson\Shipping\Model\ResourceModel\Carrier\Tableratesurepost\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Grid
 * @package Crimson\Shipping\Block\Adminhtml\Shipping\Carrier\TablerateSurepost
 */
class Grid extends Extended
{
    /**
     * Website filter
     *
     * @var int
     */
    protected $_websiteId;

    /**
     * Condition filter
     *
     * @var string
     */
    protected $_conditionName;

    /**
     * @var Tableratesurepost
     */
    protected $_tablerate;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        Tableratesurepost $tablerate,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_tablerate = $tablerate;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Define grid properties
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('shippingSurepostTablerateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Set current website
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = $this->_storeManager->getWebsite($websiteId)->getId();
        return $this;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getWebsiteId(): int
    {
        if ($this->_websiteId === null) {
            $this->_websiteId = $this->_storeManager->getWebsite()->getId();
        }

        return $this->_websiteId;
    }

    /**
     * Set current website
     *
     * @param string $name
     * @return $this
     */
    public function setConditionName($name)
    {
        $this->_conditionName = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getConditionName(): string
    {
        return $this->_conditionName;
    }

    /**
     * @return Grid
     * @throws LocalizedException
     */
    protected function _prepareCollection()
    {
        /** @var $collection CollectionFactory */
        $collection = $this->_collectionFactory->create();
        $collection->setConditionFilter($this->getConditionName())->setWebsiteFilter($this->getWebsiteId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare table columns
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'dest_country',
            ['header' => __('Country'), 'index' => 'dest_country', 'default' => '*']
        );

        $this->addColumn(
            'dest_region',
            ['header' => __('Region/State'), 'index' => 'dest_region', 'default' => '*']
        );

        $this->addColumn(
            'dest_zip',
            ['header' => __('Zip/Postal Code'), 'index' => 'dest_zip', 'default' => '*']
        );

        $label = $this->_tablerate->getCode('condition_name_short', $this->getConditionName());
        $this->addColumn('condition_value', ['header' => $label, 'index' => 'condition_value']);

        $this->addColumn('price', ['header' => __('Shipping Price'), 'index' => 'price']);

        return parent::_prepareColumns();
    }
}
