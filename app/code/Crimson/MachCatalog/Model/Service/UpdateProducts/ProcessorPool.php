<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 3:06 PM
 * @brief
 */

namespace Crimson\MachCatalog\Model\Service\UpdateProducts;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ProcessorPool
 * @package Crimson\MachCatalog\Model\Service\UpdateProducts
 */
class ProcessorPool implements ProcessorInterface
{
    /**
     * @var ProcessorInterface[]
     */
    protected $processors;

    public function __construct(
        array $processors = []
    ) {
        $this->processors = $processors;
    }

    /**
     * @param ProductInterface $product
     * @throws LocalizedException
     */
    public function process(ProductInterface $product): void
    {
        if (empty($this->processors)) {
            return;
        }

        foreach ($this->processors as $processor) {
            if (!($processor instanceof ProcessorInterface)) {
                throw new LocalizedException(__('Processor must implement %1', ProcessorInterface::class));
            }

            $processor->process($product);
        }
    }
}
