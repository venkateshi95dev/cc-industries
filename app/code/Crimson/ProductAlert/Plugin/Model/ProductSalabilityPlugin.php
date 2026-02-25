<?php

namespace Crimson\ProductAlert\Plugin\Model;

use Crimson\Catalog\Service\FullSalable;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ProductAlert\Model\ProductSalability;
use Magento\Store\Api\Data\WebsiteInterface;

class ProductSalabilityPlugin
{

    public function __construct(
        protected FullSalable $fullSalable,
    ) {}

    public function afterIsSalable(ProductSalability $subject,
                                   bool $result,
                                   ProductInterface $product,
                                   WebsiteInterface $website
    ): bool
    {
        return $result && $this->fullSalable->is($product);
    }


}
