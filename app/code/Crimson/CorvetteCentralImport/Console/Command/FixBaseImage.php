<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentralImport\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
class FixBaseImage extends Command
{

    public function __construct(
        private State $appState,
        private ResourceConnection $resource,
        private SearchCriteriaBuilder $searchCriteriaBuilder,
        private ProductRepositoryInterface $productRepository,
        private StoreManagerInterface $storeManager
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('crimson:import:fix-base-image');
        $this->setDescription('Fix missing base images for products on convette_central_website');
        $this->addOption( 'firstpage', null, InputOption::VALUE_REQUIRED, 'Process from page number', null );
        $this->addOption( 'lastpage', null, InputOption::VALUE_REQUIRED, 'Process to page number', null );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (\Exception $e) {}

        $connection = $this->resource->getConnection();

        $storeId = $this->storeManager->getStore(\Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();

        $mediaGalleryValueTable = $this->resource->getTableName('catalog_product_entity_media_gallery_value');

        // 1. Get product IDs that have gallery images
        $rowIds = $connection->fetchCol(
            "SELECT DISTINCT row_id FROM {$mediaGalleryValueTable}"
        );

        if (empty($rowIds)) {
            $output->writeln("<info>No products with images found.</info>");
            return Command::SUCCESS;
        }

        // since row_id can be different from entity_id, we need to get entity_id from catalog_product_entity table
        $productEntityTable = $this->resource->getTableName('catalog_product_entity');
        $sql = $connection->quoteInto(
            "SELECT entity_id FROM {$productEntityTable} WHERE row_id IN (?)",
            $rowIds
        );

        $idsWithImages = $connection->fetchCol($sql);


        $page = $input->getOption('firstpage')??1;
        $pageSize = 200;

        do {
            $output->writeln("<info>Page $page</info>");
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('entity_id', $idsWithImages, 'in')
                ->addFilter('store_id', $storeId)
                ->setPageSize($pageSize)
                ->setCurrentPage($page)
                ->create();

            $productList = $this->productRepository->getList($searchCriteria);
            $products = $productList->getItems();

            foreach ($products as $product) {
                $this->processProduct($product, $output);
            }

            $total = $productList->getTotalCount();
            $lastPage = $input->getOption('lastpage')??ceil($total / $pageSize);


            $output->writeln("<info>Last page $lastPage / total records $total</info>");
            $page++;
        } while ($page <= $lastPage);

        $output->writeln("<info>Fix complete.</info>");
        return Command::SUCCESS;
    }

    private function processProduct($product, OutputInterface $output)
    {
        $gallery = $product->getMediaGalleryImages();
        if (!$gallery || count($gallery) === 0) {
            return;
        }

        $base = $product->getData('image');

        // Check if base image exists in gallery
        $exists = false;
        if ($base && $base !== 'no_selection') {
            foreach ($gallery as $img) {
                if ($img->getFile() === $base) {
                    $exists = true;
                    break;
                }
            }
        }

        if ($exists) {
            return;
        }

        // Fix base image
        $first = $gallery->getFirstItem()->getFile();

        $product->setData('image', $first);
        $product->setData('small_image', $first);
        $product->setData('thumbnail', $first);

        try {
            $this->productRepository->save($product);
            $output->writeln("<info>Fixed: {$product->getSku()}</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>Error saving {$product->getSku()}: {$e->getMessage()}</error>");
        }
    }
}
