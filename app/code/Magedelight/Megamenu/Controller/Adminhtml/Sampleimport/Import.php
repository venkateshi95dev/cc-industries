<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Sampleimport;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Xml\Parser;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\BlockFactory as BlockFactory;
use Magento\Framework\App\ResourceConnection;
use Magedelight\Megamenu\Model\MenuFactory;
use Magedelight\Megamenu\Model\MenuItemsFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Filesystem\DriverInterface;
use \Magento\Framework\Exception\LocalizedException;

class Import extends Action
{
    /**
     * import directory path
     *
     * @var string
     */
    public $importPath;

    /**
     * @var Reader
     */
    protected $moduleReader;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var MenuFactory
     */
    protected $menuFactory;

    /**
     * @var MenuItemsFactory
     */
    protected $menuItemFactory;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Pool
     */
    protected $cacheFrontendPool;

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * Import constructor.
     *
     * @param Context $context
     * @param Parser $parser
     * @param BlockRepositoryInterface $blockRepository
     * @param BlockFactory $blockFactory
     * @param ResourceConnection $resource
     * @param MenuFactory $menuFactory
     * @param MenuItemsFactory $menuItemFactory
     * @param ProductMetadataInterface $productMetadata
     * @param Reader $moduleReader
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     * @param DriverInterface $driver
     */
    public function __construct(
        Context $context,
        Parser $parser,
        BlockRepositoryInterface $blockRepository,
        BlockFactory $blockFactory,
        ResourceConnection $resource,
        MenuFactory $menuFactory,
        MenuItemsFactory $menuItemFactory,
        ProductMetadataInterface $productMetadata,
        Reader $moduleReader,
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        DriverInterface $driver
    ) {
        parent::__construct($context);
        $this->moduleReader = $moduleReader;
        $etcDir = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
            'Magedelight_Megamenu'
        );
        $this->importPath = $etcDir . '/import/';
        $this->parser = $parser;
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
        $this->resource = $resource;
        $this->menuFactory = $menuFactory;
        $this->menuItemFactory = $menuItemFactory;
        $this->productMetadata = $productMetadata;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->driver = $driver;
    }

    /**
     * Imports country list from csv file
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $data = $this->getRequest()->getPostValue();
            $this->importCms($data);

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }

    /**
     * Import Cms
     *
     * @param array $importdata
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function importCms($importdata)
    {
        $xmlPath = $this->importPath . 'import.xml';
        $fileData = $this->getRequest()->getFiles('import_file');

        if (isset($fileData) && !empty($fileData['name'])) {

            // Validate file type (ensure it's XML)
            $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
            if (strtolower($fileExtension) !== 'xml') {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Only XML files are allowed.')
                );
            }
            $xmlPath = $fileData['tmp_name'];
        }

        $overwrite = false;
        $path = 'magedelight/general/primary_menu';

        if ($importdata['override'] == '1') {
            $overwrite = true;
        }

        if (!$this->driver->isReadable($xmlPath)) {
            throw new LocalizedException(
                __("Can't get the data file for import : " . $xmlPath)
            );
        }
        $data = $this->parser->load($xmlPath)->xmlToArray();
        $conflictingOldItems = [];
        $i = 0;
        $blocksItem = isset($data['root']['blocks']['item'][0]) ?
            $data['root']['blocks']['item'] : [];

        if(count($blocksItem)) {
            foreach ($blocksItem as $_item) {
                $exist = $this->blockFactory->create()->getCollection()
                        ->addFieldToFilter('identifier', $_item['identifier'])
                        ->getSize() > 0;

                if ($exist) {
                    $conflictingOldItems[] = $_item['identifier'];
                    if ($overwrite) {
                        $this->blockRepository->deleteById($_item['identifier']);
                    } else {
                        continue;
                    }
                }

                $_item['stores'] = [0];
                if (version_compare($this->productMetadata->getVersion(), '2.2.0', '>=') &&
                    ($_item['identifier'] === 'menudemo-1-column-6-products' ||
                        $_item['identifier'] === 'menudemo-1-column-3-products')) {
                    $_item['content'] = ($_item['identifier'] === 'menudemo-1-column-6-products')
                        ? '{{widget type="Magento\CatalogWidget\Block\Product\ProductsList"' .
                        'show_pager="0" products_count="5" template="product/widget/content/grid.phtml"' .
                        'conditions_encoded="^[`1`:^[`type`:' .
                        '`Magento||CatalogWidget||Model||Rule||Condition||Combine`,' .
                        '`aggregator`:`all`,`value`:`1`,`new_child`:``^]^]"}}'
                        : '<div class="product-column-count3">' .
                        '<h4>Hot Product</h4>' .
                        '{{widget type="Magento\CatalogWidget\Block\Product\ProductsList"' .
                        ' show_pager="0" products_count="3" template="product/widget/content/grid.phtml"' .
                        ' conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule' .
                        '||Condition||Combine`,' .
                        '`aggregator`:`all`,`value`:`1`,`new_child`:``^]^]"}}</div>';
                }

                $this->blockFactory->create()->setData($_item)->save();
                $i++;
            }
        }

        $menuItems = isset($data['root']['menus']['item'][0]) ?
            $data['root']['menus']['item'] : [$data['root']['menus']['item']];
        foreach ($menuItems as $_item) {

            $menu_collection = $this->menuFactory->create()->getCollection()
                    ->addFieldToFilter('menu_name', $_item['menu_name']);

            if ($importdata['override'] == '1' && !empty($menu_collection->getData())) {
                continue;
            }

            $_item['store_id'] = [0];
            $menuid = $_item['menu_id'];
            unset($_item['menu_id']);
            $menu = $this->menuFactory->create()->setData($_item)->save();
            if ($menu->getMenuName() == 'Horizontal Fixed Category View'
            && $menu->getMenuDesignType() == 'horizontal') {
                $this->configWriter->save(
                    $path,
                    $menu->getMenuId(),
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    0
                );
            }
            foreach ($data['root']['menu_items']['item'] as $sub_item) {
                if ($menuid == $sub_item['menu_id']) {
                    $sub_item['menu_id'] = $menu->getMenuId();
                    $this->menuItemFactory->create()->setData($sub_item)->save();
                }
            }
            $i++;
        }

        $message = "";
        if ($i) {
            $this->cleanCache();
            $this->messageManager->addSuccessMessage(__($i . " item(s) was(were) imported."));
        } else {
            $this->messageManager->addErrorMessage(__("No items were imported."));
        }

        if ($overwrite) {
            if ($conflictingOldItems) {
                $message .= "Items (" .
                count($conflictingOldItems) . ") with the following identifiers were overwritten:<br/>"
                . implode('<br> ', $conflictingOldItems);
                $this->messageManager->addNoticeMessage(__($message));
            }
        } else {
            if ($conflictingOldItems) {
                $message .= "<br/>Unable to import items (" .
                count($conflictingOldItems)
                . ") with the following identifiers (they already exist in the database):<br/>"
                . implode(', ', $conflictingOldItems);
                $this->messageManager->addNoticeMessage(__($message));
            }
        }
    }

    /**
     * Clean Cache
     *
     * @return void
     */
    public function cleanCache()
    {
        $types = ['config'];
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
