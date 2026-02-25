<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/15/2019 1:44 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class AddCatalogRequestStaticBlocks implements
    DataPatchInterface,
    PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @param ModuleDataSetupInterface        $moduleDataSetup
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        $content =<<<'HTML'
<h1>Free Parts and Accessories Catalogs</h1>
<div>
<p>What began as a few photocopied loose sheets has evolved into generation specific, full-color Corvette parts catalogs we believe are the best out there. For the restoration crowd, we've taken a fresh approach: build a book same as one would build a Corvette. More than just hundreds of Corvette part listings, photos, and helpful reference illustrations, you'll find everything presented in an easy-to-follow order that mimics the logical progression of a bare frame build-up. C5, C6 and C7-era enthusiasts know Zip's Corvette parts catalogs to be an exciting mix of interior, exterior, and underhood enhancements, performance bolt-ons, and Corvette-theme apparel and accessories. If you don't see what you're looking for in any edition, call or shop our Web store &ndash; we're adding new parts and accessories all the time!</p>
</div>
<div class="alert alert-info">
<h3>Download our Parts Catalogs now:</h3>
<p>We ship catalogs once a week; so you don't have to wait long to start putting your parts list together. However, we know some of you don't want to wait for the mail. We have our catalogs available in .pdf format below. You will need the free program <a title="Adobe Reader" href="http://get.adobe.com/reader/" target="_blank">Adobe Acrobat Reader</a> to view them.</p>
<p><a title="Download C1 Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/c1-parts-catalog.pdf" target="_blank">53-62 C1 Corvette</a> | <a title="Download C2 Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/c2-parts-catalog.pdf" target="_blank">63-67 C2 Corvette</a> | <a title="Download C3 Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/c3-parts-catalog.pdf" target="_blank">68-82 C3 Corvette</a> | <a title="Download C4 Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/c4-parts-catalog.pdf" target="_blank">84-96 C4 Corvette</a> | <a title="Download Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/c5-parts-catalog.pdf" target="_blank">97-04 C5 Corvette</a> | <a title="Download C6 Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/c6-parts-catalog.pdf" target="_blank">05-13 C6 Corvette</a>&nbsp;| <a title="Download C7 Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/c7-parts-catalog.pdf" target="_blank">14-19 C7 Corvette</a></p>
</div>
HTML;

        $cmsBlock = $this->blockFactory->create();
        $cmsBlock = $cmsBlock->load('catalog-request-top-custom-block', 'identifier');
        $cmsBlock->setTitle('Catalog Request')
            ->setIdentifier('catalog-request-top-custom-block')
            ->setContent($content)
            ->setIsActive(1)
            ->setStores([0]);

        $cmsBlock->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.3.0';
    }
}
