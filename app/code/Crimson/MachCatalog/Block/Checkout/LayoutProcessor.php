<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 10:19 PM
 * @brief
 */

namespace Crimson\MachCatalog\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Phrase;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class LayoutProcessor
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var GetBlockByIdentifierInterface
     */
    protected $getBlockByIdentifier;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * LayoutProcessor constructor.
     *
     * @param GetBlockByIdentifierInterface $getBlockByIdentifier
     * @param StoreManagerInterface                          $storeManager
     */
    public function __construct(
        GetBlockByIdentifierInterface $getBlockByIdentifier,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->getBlockByIdentifier = $getBlockByIdentifier;
        $this->storeManager         = $storeManager;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout): array
    {
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['before-shipping-method-form']['children']
        ['mach_hsc']['description'] = $this->_getCartPageBackorderBlockContent();

        return $jsLayout;
    }

    /**
     * @return Phrase|string|null
     */
    protected function _getCartPageBackorderBlockContent()
    {
        try {
            $storeId = $this->storeManager->getStore(true)->getId();

            $block = $this->getBlockByIdentifier->execute('backorder_shipments_checkout', $storeId);

            return $block->getContent();
        } catch (\Exception $e) {
            return __('...');
        }
    }
}
