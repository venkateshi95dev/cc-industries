<?php
/**
 * Crimson Agility, LLC
 */
namespace Crimson\Category\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddC8MenuItem implements DataPatchInterface
{
    /**
     * Block factory.
     *
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    private $blockCollectionFactory;

    /**
     * Block factory.
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var \Magedelight\Megamenu\Model\MenuItemsFactory
     */
    private $menuItemsFactory;

    /**
     * @var \Magedelight\Megamenu\Model\ResourceModel\MenuItems\CollectionFactory
     */
    private $menuItemsCollectionFactory;

    /**
     * AddC8MenuItem constructor.
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
     * @param \Magedelight\Megamenu\Model\MenuItemsFactory $menuItemsFactory
     * @param \Magedelight\Megamenu\Model\ResourceModel\MenuItems\CollectionFactory $menuItemsCollectionFactory
     */
    public function __construct(
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
        \Magedelight\Megamenu\Model\MenuItemsFactory $menuItemsFactory,
        \Magedelight\Megamenu\Model\ResourceModel\MenuItems\CollectionFactory $menuItemsCollectionFactory
    ) {
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->blockFactory = $blockFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->blockRepository = $blockRepository;
        $this->menuItemsFactory = $menuItemsFactory;
        $this->menuItemsCollectionFactory = $menuItemsCollectionFactory;
    }

    public function apply()
    {
        $search = $this->searchCriteriaBuilder->addFilter('identifier', 'megamenu_12', 'eq')->create();
        $cmsblocks = $this->blockRepository->getList($search)->getItems();
        if (!count($cmsblocks)) {

            /** @var \Magento\Cms\Model\Block $block */
            $block = $this->blockFactory->create();

            $block->setTitle('Megamenu 12th Dropdown')
                ->setIsActive(true)
                ->setIdentifier('megamenu_12')
                ->setContent(
                    <<<EOT
<div class="row">
    <div class="col-md-6">
        <a href="https://www.zip-corvette.com/14-19-c7.html" title="C7 Corvette Parts" style="text-align: center;"><h2 style="font-size: 2em;margin-bottom: 0;color: #0a4f91;">C8 Corvette Parts</h2></a>
        <div class="col-md-4 first-column">
            <ul class="menu-column">
                <li><a title="Performance Exhaust" href="https://www.zip-corvette.com/20-c8/exhaust.html">Performance Exhaust</a></li>
                <li><a title="Interior Upgrades" href="https://www.zip-corvette.com/corvette-brands/sandyeggo-designs.html">Interior Upgrades</a></li>
                <li><a title="Shifter Knobs" href="https://www.zip-corvette.com/20-c8/corvette-shifter-transmission.html">Shifter Knobs</a></li>
                <li><a title="Performance Air Filters" href="https://www.zip-corvette.com/corvette-brands/green-air-filters.html">Performance Air Filters</a></li>
                <li><a title="Engine Dress-Up" href="https://www.zip-corvette.com/20-c8/engine-dress-up.html">Engine Dress-Up</a></li>
                <li><a title="Performance Headers" href="https://www.zip-corvette.com/corvette-brands/hooker-blackheart.html">Performance Headers</a></li>
                <li><a title="C7 Shop & Service Manuals" href="https://www.zip-corvette.com/books-manuals/corvette-shop-service-manuals/c7-shop-service-manuals.html">C7 Shop & Service Manuals</a></li>
            </ul>
        </div>
        <div class="col-md-4">
            <ul class="menu-column">
                <li><a href="{{store url=''}}20-c8/body.html">Body</a></li>
                <li><a href="{{store url=''}}20-c8/corvette-brakes.html">Brakes</a></li>
                <li><a href="{{store url=''}}20-c8/corvette-car-covers.html">Car Covers</a></li>
                <li><a href="{{store url=''}}20-c8/corvette-cooling-system.html">Cooling System</a></li>
                <li><a href="{{store url=''}}20-c8/engine-performance.html">Engine &amp; Performance</a></li>
                <li><a href="{{store url=''}}20-c8/engine-dress-up.html">Engine Dress Up</a></li>
                <li><a href="{{store url=''}}20-c8/exhaust.html">Exhaust</a></li>
                <li><a href="{{store url=''}}20-c8/exterior-accessories.html">Exterior Accessories</a></li>
                <li><a href="{{store url=''}}20-c8/interior-accessories.html">Interior Accessories</a></li>
                <li><a href="{{store url=''}}20-c8/corvette-personal-accessories.html">Personal Accessories</a></li>
            </ul>
        </div>
        <div class="col-md-4">
            <ul class="menu-column">
                <li><a href="{{store url=''}}20-c8/corvette-shifter-transmission.html">Shifter, Transmission & Driveline</a></li>
                <li><a href="{{store url=''}}20-c8/corvette-suspension.html">Suspension</a></li>
                <li><a href="{{store url=''}}20-c8/corvette-wheels-tires.html">Wheels and Tires</a></li>
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-6">
        </div>
        <div class="col-md-6">
        </div>
    </div>
</div>
EOT
                );

            $this->blockRepository->save($block);

            /** @var \Magedelight\Megamenu\Model\ResourceModel\MenuItems\Collection $collection */
            $collection = $this->menuItemsCollectionFactory->create();

            $collection->addFieldToFilter('sort_order', ['gt' => 7]);

            foreach ($collection as $menuItem) {
                $menuItem->setSortOrder($menuItem->getSortOrder() + 1);
                $menuItem->save();
            }

            /** @var \Magedelight\Megamenu\Model\MenuItems $menuItem */
            $menuItem = $this->menuItemsFactory->create();

            $menuItem->setItemName('20 C8')
                ->setItemType('megamenu')
                ->setSortOrder(8)
                ->setMenuId(2)
                ->setItemColumns(
                    json_encode([
                        [
                        'type' => 'block',
                        'value' => 'megamenu_12',
                        'showtitle' => "0"
                        ]
                    ])
                );
            $menuItem->setAnimationOption('undefined');

            $menuItem->save();
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
            AddC8Category::class
        ];
    }
}