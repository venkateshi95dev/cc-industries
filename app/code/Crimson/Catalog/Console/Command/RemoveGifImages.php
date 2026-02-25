<?php

namespace Crimson\Catalog\Console\Command;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Catalog\Model\Product\Gallery\Processor as ImageProcessor;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Area;

/**
 * Class RemoveGifImages
 * @package Crimson\Catalog\Console\Command
 */
class RemoveGifImages extends Command
{

    CONST GIF_EXT = "gif";
    CONST MEDIA_CATALOG_PATH = 'pub/media/catalog/product';

    /**
     * @var ProductCollection
     */
    protected $productCollection;

    /**
     * @var ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepositoryInterface;

    /**
     * @var Emulation
     */
    protected $emulation;

    public function __construct(
        ProductCollection $productCollection,
        ImageProcessor $imageProcessor,
        ProductRepositoryInterface $productRepositoryInterface,
        Emulation $emulation
    ) {
        $this->productCollection = $productCollection;
        $this->imageProcessor = $imageProcessor;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->emulation = $emulation;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setName('crimson:catalog:remove-gif-images');
        $this->setDescription('Remove the GIF images that are not set as Base/Small/Thumb.');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->emulation->startEnvironmentEmulation(0,Area::AREA_ADMINHTML);
        // initial message
        $output->writeln("Looking for products containing GIF images....");
        $products = $this->getCandidateProducts();
        $size = $products->count();
        if ($size > 0) {
            $output->writeln($size . " products were found with .gif images.");
            $output->writeln("Starting to process them.");

            foreach ($products as $product) {
                $output->writeln("Analyzing product: " . $product->getSku());
                /** @var Product $product */
                $mediaGallery = $product->getMediaGalleryEntries();
                $needToSave = false;
                foreach ($mediaGallery as $key => $image) {
                    if ($image->getMediaType() != 'image' || !isset($image['file'])) {
                        continue;
                    }
                    if (substr(strtolower($image->getFile()), -3) != self::GIF_EXT ) {
                        continue;
                    }
                    if ($image->getFile() == $product->getImage() ||
                        $image->getFile() == $product->getSmallImage() ||
                        $image->getFile() == $product->getThumbnail()
                    ) {
                        continue;
                    }

                    //at this point image is a .gif and not the base/small/thumb so we remove it
                    $this->imageProcessor->removeImage($product,$image->getFile());
                    unset($mediaGallery[$key]);
                    if(file_exists(self::MEDIA_CATALOG_PATH . $image->getFile())) {
                        unlink(self::MEDIA_CATALOG_PATH . $image->getFile());
                    }

                    $needToSave = true;
                }

                //saving the product
                if ($needToSave) {
                    $this->productRepositoryInterface->save($product);
                }
            }
            $output->writeln("Finishing the process.");

        } else {
            $output->writeln("No products were found with .gif images.");
            $output->writeln("Finishing.");
        }
        $this->emulation->stopEnvironmentEmulation();
    }


    /**
     * @return mixed
     */
    protected function getCandidateProducts()
    {
        $collection = $this->productCollection->create()
            ->addAttributeToSelect(['row_id', 'sku', 'name', 'image', 'small_image', 'thumbnail']);

        //Join with Media Gallery Tables to get Products with .gif images in their galleries
        $collection->getSelect()->joinInner(
            ['c_p_e_m_g_v' => $collection->getTable('catalog_product_entity_media_gallery_value')],
            'e.row_id = c_p_e_m_g_v.row_id'
        );

        $collection->getSelect()->joinInner(
            ['c_p_e_m_g' => $collection->getTable('catalog_product_entity_media_gallery')],
            'c_p_e_m_g_v.value_id = c_p_e_m_g.value_id'
        );
        $collection->getSelect()->where('c_p_e_m_g.value LIKE "%.gif"');
        $collection->getSelect()->group('e.row_id');

        //adding the media gallery data
        $collection->addMediaGalleryData();

        return $collection;
    }
}
