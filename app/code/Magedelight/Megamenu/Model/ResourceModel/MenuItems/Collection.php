<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model\ResourceModel\MenuItems;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magedelight\Megamenu\Model\MenuItems as MenuItemsModel;
use Magedelight\Megamenu\Model\ResourceModel\MenuItems as MenuItemsResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Megamenu\Helper\Data
     */
    protected $helper;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magedelight\Megamenu\Helper\Data $helper
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory,
        \Psr\Log\LoggerInterface                                     $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface                    $eventManager,
        \Magento\Store\Model\StoreManagerInterface                   $storeManager,
        \Magento\Framework\EntityManager\MetadataPool                $metadataPool,
        \Magedelight\Megamenu\Helper\Data                            $helper,
        ?\Magento\Framework\DB\Adapter\AdapterInterface              $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {

        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        $this->helper = $helper;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            MenuItemsModel::class,
            MenuItemsResourceModel::class
        );
    }

    /**
     * After Load
     *
     * @return AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _afterLoad()
    {
        if (!$this->helper->permissionEnabled() || $this->helper->getArea() == 'adminhtml') {
            return parent::_afterLoad();
        }
        $excludeCategoryIds = $this->helper->getExcludeCategoryIds();
        foreach ($this->_items as $key => $item) {
            if ($item->getItemType() == 'category') {
                if (in_array($item->getObjectId(), $excludeCategoryIds)) {
                    unset($this->_items[$key]);
                }
            }
        }
        return parent::_afterLoad();
    }
}
