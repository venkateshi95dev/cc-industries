<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 3:10 PM
 * @brief
 */

namespace Crimson\MachCatalog\Model\Service\UpdateProducts;

use Crimson\Catalog\Model\Service\UrlKey;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Url;
use Psr\Log\LoggerInterface;

/**
 * Class ProcessUrl
 * @package Crimson\MachCatalog\Model\Service\UpdateProducts
 */
class ProcessUrl implements ProcessorInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var Url
     */
    protected $productUrlModel;
    /**
     * @var UrlKey
     */
    protected $urlKey;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Url $productUrlModel,
        LoggerInterface $logger,
        UrlKey $urlKey
    ) {
        $this->productRepository = $productRepository;
        $this->productUrlModel   = $productUrlModel;
        $this->logger            = $logger;
        $this->urlKey = $urlKey;
    }

    /**
     * @param ProductInterface|Product $product
     */
    public function process(ProductInterface $product): void
    {
        $this->logger->debug(__('Analyzing product - Entity Id: %1', $product->getId()));
        $normalizedUrlKey = $this->productUrlModel->formatUrlKey($product->getName());
        $normalizedUrlKey = $this->urlKey->makeUniqueUrlKey($product->getSku(), $normalizedUrlKey);
        $currentUrlKey    = $product->getUrlKey();

        if (strcmp($normalizedUrlKey, $currentUrlKey) !== 0) {
            $product->setUrlKey($normalizedUrlKey);
            $product->setData('save_rewrites_history', true);
            try {
                $this->productRepository->save($product);
                $this->logger->debug(__('Modified URL KEY for product - Entity Id: %1', $product->getId()));
            } catch (\Exception $e) {
                $this->logger->debug(
                    __('Skipping product Entity Id: %1.  Error: %2' . $product->getId(), $e->getMessage())
                );
            }
        } else {
            $this->logger->debug(__('There were no changes for this product - Entity Id: %1', $product->getId()));
        }
    }
}
