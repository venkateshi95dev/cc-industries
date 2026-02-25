<?php
declare(strict_types=1);

namespace Crimson\Catalog\ViewModel;

use Crimson\Catalog\Model\Config;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Availability implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config
    ) {
    }

    /**
     * @param ProductInterface $product
     * @return string
     */
    public function getAvailabilityLabel(ProductInterface $product): string
    {
        $label = '';
        $messagesConfiguration = $this->config->getAvailabilityMessages();
        foreach ($messagesConfiguration ?: [] as $message) {
            if (($message['item_discount_group'] ?? false) === $product->getData('item_discount_group')) {
                if (!($label = $message['message'])) {
                    $label = $this->config->getDefaultAvailabilityMessage();
                }
                return $label;
            }
        }
        return $label;
    }

    /**
     * @param ProductInterface $product
     * @return bool
     */
    public function isProductAvailable(ProductInterface $product): bool
    {
        $messagesConfiguration = $this->config->getAvailabilityMessages();
        foreach ($messagesConfiguration ?: [] as $message) {
            if (($message['item_discount_group'] ?? false) === $product->getData('item_discount_group')) {
                return false;
            }
        }
        return true;
    }
}
