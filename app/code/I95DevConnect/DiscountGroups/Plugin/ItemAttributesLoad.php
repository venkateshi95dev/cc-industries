<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Plugin;

use Magento\Sales\Api\Data\OrderItemExtensionInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;

class ItemAttributesLoad
{
    /**
     * @var OrderItemExtensionFactory
     */
    private $extensionFactory;

    /**
     * @param OrderItemExtensionFactory $extensionFactory
     */
    public function __construct(OrderItemExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Loads OrderItem entity extension attributes
     *
     * @param OrderItemInterface $entity
     * @param OrderItemExtensionInterface|null $extension
     * @return OrderItemExtensionInterface
     */
    public function afterGetExtensionAttributes(
        OrderItemInterface $entity, //NOSONAR
        OrderItemExtensionInterface $extension = null
    ) {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }
        return $extension;
    }
}
