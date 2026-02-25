<?php

namespace Crimson\Cms\Setup\Patch\Data;

use Magento\Cms\Model\Block;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;

class UpdateTopHeaderLinksBlock20211206 implements DataPatchInterface
{
    const IDENTIFIER = 'top-header-links';
    const TITLE = 'Top Header Links';
    const STATUS = Block::STATUS_ENABLED;
    const STORE_IDS = [Store::DEFAULT_STORE_ID];
    const CONTENT_FILE_PATH = 'block/top-header-links.html';

    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface */
    protected $_setup;

    /** @var \Magento\Cms\Api\Data\BlockInterfaceFactory */
    protected $_blockFactory;

    /** @var \Magento\Cms\Api\BlockRepositoryInterface */
    protected $_blockRepository;

    /**
     * AddFooterBlock constructor.
     * @param StoreManagerInterface $storeManager
     * @param BlockInterfaceFactory $blockFactory
     * @param BlockRepositoryInterface $blockRepository
     * @param LoggerInterface $logger
     * @param Reader $moduleReader
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Cms\Api\Data\BlockInterfaceFactory $blockFactory,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
    ) {
        $this->_setup = $setup;
        $this->_blockFactory = $blockFactory;
        $this->_blockRepository = $blockRepository;
    }

    public function apply()
    {
        $this->_setup->startSetup();
        
        $setupDataDir = __DIR__ . '/../../data/';
        $content = file_get_contents($setupDataDir . self::CONTENT_FILE_PATH);

        try {
            $block = $this->_blockRepository->getById(self::IDENTIFIER);
        } catch (NoSuchEntityException $e) {
            $block = $this->_blockFactory->create();
            $block->setIdentifier(self::IDENTIFIER);
        }
        $block->setTitle(self::TITLE);
        $block->setStatus(self::STATUS);
        $block->setStoreId(self::STORE_IDS);
        $block->setContent($content);
        $this->_blockRepository->save($block);

        $this->_setup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}