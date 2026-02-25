<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/15/2019 5:56 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class AddCatalogRequestCmsPage implements
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
    protected $pageFactory;

    /**
     * @param ModuleDataSetupInterface        $moduleDataSetup
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->pageFactory     = $pageFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        $content = <<<'HTML'
<span class="lead">Catalog Request Successfully Received</span>
<p><img style="font-family: 'PT Sans', Helvetica, Calibri, Arial, sans-serif; line-height: 18px; float: left;" title="Catalog Request Confirmation and Download" alt="Catalog Request Confirmation and Download" src="http://hosting-source.bm23.com/18315/public/catalog-request-confirmation-catalogs.jpg" height="261" width="275" />Thank you for requesting a Corvette Parts and Accessories catalog! Each of our catalogs is a valuable resource that includes page after page of the newest-available and best-quality Corvette parts to be had.</p>
<p>You can find everything that's in our catalog on our website as well. Our website is a veritable gold mine of full color pictures, expanded part descriptions, helpful assembly illustrations and parts install guidance. Best of all, it's open all day, every day.</p>
<p><a title="Catalog Download" href="http://www.zip-corvette.com/request-catalog"><img style="font-family: 'PT Sans', Helvetica, Calibri, Arial, sans-serif; line-height: 18px; float: right;" title="Catalog Request Confirmation and Download" alt="Catalog Request Confirmation and Download" src="http://hosting-source.bm23.com/18315/public/catalog-request-confirmation-download-button.jpg" height="175" width="266" /></a><br /> Have a question that's not answered online or in-catalog? Pick up the phone and give us a call. Our sales team boasts more than 100 years of collective Corvette knowledge. We can answer just about any Corvette question and if we don't have the answer you need, we know someone who does. Once you're ready to make your purchase, you can place an order with us online at your convenience or call us at 1.800.962.9632 during regular business hours.</p>
<p>Thank you for choosing Zip Corvette. We look forward to helping you restore, maintain or personalize your Corvette.</p>
HTML;

        $storeIds = [1];

        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->pageFactory->create();

        $page->load('catalog-request-success.html', 'identifier');
        $page->setTitle('Catalog Request Success')
            ->setIdentifier('catalog-request-success.html')
            ->setIsActive(true)
            ->setPageLayout('1column')
            ->setStores($storeIds)
            ->setContent($content)
            ->save();


        $page->save();
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
