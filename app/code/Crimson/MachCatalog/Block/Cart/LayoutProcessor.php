<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 10:09 PM
 * @brief
 */

namespace Crimson\MachCatalog\Block\Cart;

use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Framework\Phrase;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class LayoutProcessor
 * @package Crimson\MachCatalog\Block\Cart
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var GetBlockByIdentifierInterface
     */
    protected $getBlockByIdentifier;
    /**
     * @var AttributeMerger
     */
    protected $merger;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        AttributeMerger $merger,
        GetBlockByIdentifierInterface $getBlockByIdentifier,
        StoreManagerInterface $storeManager
    ) {
        $this->merger               = $merger;
        $this->getBlockByIdentifier = $getBlockByIdentifier;
        $this->storeManager         = $storeManager;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     *
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function process($jsLayout): array
    {
        $jsLayout['components']['block-summary']['children']['block-shipping']['children']['address-fieldsets']
        ['children']['mach_hsc']['description'] = $this->_getCartPageBackorderBlockContent();

        return $jsLayout;
    }

    /**
     * @return Phrase|string|null
     */
    protected function _getCartPageBackorderBlockContent()
    {
        try {
            $storeId = $this->storeManager->getStore(true)->getId();
            $block = $this->getBlockByIdentifier->execute('backorder_shipments', $storeId);

            return $block->getContent();
        } catch (\Exception $e) {
            return __('...');
        }
    }
}
