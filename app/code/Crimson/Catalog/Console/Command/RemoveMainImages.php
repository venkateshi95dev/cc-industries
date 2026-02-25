<?php

namespace Crimson\Catalog\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\Area;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Catalog\Model\Product\Gallery\Processor as ImageProcessor;
use Magento\Framework\App\State;

/**
 * Class RemoveMainImages
 * @package Crimson\Catalog\Console\Command
 */
class RemoveMainImages extends Command
{

    CONST CSV_FILE_PATH_NAME = "/var/import/ImagesToDelete/sku_images_delete.csv";
    CONST MEDIA_CATALOG_PATH = 'pub/media/catalog/product';

    /**
     * @var Csv
     */
    protected $csvProcessor;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepositoryInterface;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @var State
     */
    protected $state;

    /**
     * RemoveMainImages constructor.
     * @param Csv $csvProcessor
     * @param DirectoryList $directoryList
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ImageProcessor $imageProcessor
     * @param State $state
     */
    public function __construct(
        Csv $csvProcessor,
        DirectoryList $directoryList,
        ProductRepositoryInterface $productRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ImageProcessor $imageProcessor,
        State $state
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->imageProcessor = $imageProcessor;
        $this->state = $state;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setName('crimson:catalog:remove-main-images');
        $this->setDescription('Remove the Base/Small/Thumb images physically and from the DB.');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // intentionally left empty
        }
        try {
            // initial message
            $output->writeln("*** Looking for a CSV file... ***");

            // get root path
            $rootPath = $this->directoryList->getRoot();
            // get file path
            $filename = $rootPath . self::CSV_FILE_PATH_NAME;
            // insert data from file to an array
            $skuList = $this->csvProcessor->getData($filename);
            // remove header data from array
            array_shift($skuList);
            if (count($skuList) > 0) {
                // start processing data
                $output->writeln(count($skuList) . " SKUs found in the CSV.");
                $output->writeln("Looking for matching products.");
                //looking for candidates
                $skuListFinal = $this->getCandidateProducts($skuList);
                if ($skuListFinal) {
                    $output->writeln($skuListFinal->getTotalCount(). " products found.");
                    //analyzing products
                    /** @var \Magento\Catalog\Model\Product $product */
                    foreach ($skuListFinal->getItems() as $product) {
                        $output->writeln("Analyzing product: " . $product->getSku());
                        $mediaGallery = $product->getMediaGalleryEntries();
                        $needToSave = false;
                        foreach ($mediaGallery as $key => $image) {
                            if ($image->getMediaType() != 'image' || !isset($image['file'])) {
                                continue;
                            }
                            if ($image->getFile() == $product->getImage() ||
                                $image->getFile() == $product->getSmallImage() ||
                                $image->getFile() == $product->getThumbnail()
                            ) {
                                //here we have any of the 3 types: base/small/thumb so we remove it
                                $this->imageProcessor->removeImage($product,$image->getFile());
                                unset($mediaGallery[$key]);
                                if(file_exists(self::MEDIA_CATALOG_PATH . $image->getFile())) {
                                    unlink(self::MEDIA_CATALOG_PATH . $image->getFile());
                                }

                                $needToSave = true;
                            }
                        }

                        //saving the product
                        if ($needToSave) {
                            $output->writeln("Images small/base/thumb removed, now saving the product: " . $product->getSku());
                            $this->productRepositoryInterface->save($product);
                        } else {
                            $output->writeln("No small/base/thumb images to remove");
                        }
                    }

                } else {
                    $output->writeln("Warning: No products found.");
                }
            } else {
                $output->writeln("Warning: No SKUs found.");
            }

        } catch (\Exception $e) {
            $output->writeln("ERROR: The process failed. Please check the CSV.");
        } finally {
            $output->writeln("*** Finished!! ***");
        }
    }

    /**
     * @param array $skuList
     * @return SearchResultsInterface|null
     */
    protected function getCandidateProducts(array $skuList): ?SearchResultsInterface
    {
        if (empty($skuList) || !is_array($skuList)) {
            return null;
        }

        $this->searchCriteriaBuilder->addFilter('sku', $skuList, 'in');
        $productsSearch = $this->productRepositoryInterface->getList($this->searchCriteriaBuilder->create());
        if (!$productsSearch->getTotalCount()) {
            return null;
        }

        return $productsSearch;
    }

}
