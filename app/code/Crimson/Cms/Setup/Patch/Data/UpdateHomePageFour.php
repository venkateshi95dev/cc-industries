<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        4/10/2019 7:59 AM
 * @brief
 */
namespace Crimson\Cms\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;

class UpdateHomePageFour implements
    DataPatchInterface,
    PatchVersionInterface

{
    /**
     * Page factory.
     *
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * Block factory.
     *
     * @var BlockCollectionFactory
     */
    private $blockCollectionFactory;

    /**
     * Block factory.
     *
     * @var BlockFactory
     */
    private $blockFactory;


    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory
    )
    {
        $this->pageFactory = $pageFactory;
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->blockFactory = $blockFactory;

    }

    public function apply()
    {
        $content = '<div class="owl-carousel home-slider">
<div class="item"><a title="American Car Craft" href="https://www.zip-corvette.com/corvette-brands/american-car-craft.html">
<img src="{{media url=&quot;home/top_row_ads/acc-home-slider-1875x379.jpg&quot;}}" alt="american car craft" /></a></div>
<div class="item"><a title="Antique Auto Radio" href="https://www.zip-corvette.com/corvette-brands/antique-auto-radio.html">
<img src="{{media url=&quot;home/main/corvette-radios-slider-1875x379.jpg&quot;}}" alt="Antique Auto Radio" /></a></div>
<div class="item"><a title="Apparel" href="https://www.zip-corvette.com/apparel.html">
<img src="{{media url=&quot;home/main/corvette-apparel-slider-1875x379.jpg&quot;}}" alt="Apparel" /></a></div>
<div class="item"><a title="Corsa Exhaust Systems" href="https://www.zip-corvette.com/corvette-brands/corsa-performance-exhaust.html"><img src="{{media url=&quot;home/main/corsa-home-slider-1875x379.jpg&quot;}}" alt="corsa exhaust systems"></a></div>
</div>
<div class="top-section">
<h2>Corvette Parts and Accessories by Zip. Corvettes are all we do!</h2>
<h2><strong>Need Help? 1-800-962-9632</strong></h2>
</div>
<div class="row advertisment-1">
<div class="col-md-4 col-sm-6 col-xs-12"><a title="gift cards" href="https://www.zip-corvette.com/accessories/corvette-gift-cards.html"><img src="{{media url=&quot;home/top_row_ads/zip-gift-cards-blocks-612x260.jpg&quot;}}" alt="gift cards"></a></div>
<div class="col-md-4 col-sm-6 col-xs-12"><a title="fi tech" href="https://www.zip-corvette.com/corvette-brands/fitech-fuel-injection.html"><img src="{{media url=&quot;home/top_row_ads/fitech-fuel-612x260-new.jpg&quot;}}" alt="fi tech" /></a></div>
<div class="col-md-4 col-sm-0 col-xs-12"><a title="our story" href="https://www.zip-corvette.com/our-story.html"><img src="{{media url=&quot;home/ad-1-1.png&quot;}}" alt=""></a></div>
</div>
<div class="row form row-eq-height"><!-- form -->
<div class="col-lg-6 newsletter-form">
<h3><strong>Our Newsletter</strong></h3>
<p>Sign up and you\'ll be automatically entered to win a <strong>$50 gift card</strong> plus we\'ll send you subscriber-only discounts and more.</p>
<form action="//email.zip-corvette.com/public/webform/process/" method="post"><input name="fid" type="hidden" value="41vrbeqb6z4zxjemu5clxev2uwgz6"> <input name="sid" type="hidden" value="2cdb8ef4bb49bd5a3a5c35c299d7a019"> <input name="delid" type="hidden" value=""> <input name="subid" type="hidden" value=""> <input name="td" type="hidden" value=""> <input name="formtype" type="hidden" value="addcontact">
<div id="row_17292" class="section">
<div id="column_21778">
<div>Please select your Corvette generation(s) below:</div>
</div>
<div style="clear: both;">&nbsp;</div>
</div>
<div id="row_17293" class="section">
<div class="field_block" style="display: inline-block; width: 13%;">
<div class="checkbox field"><input id="field_64625" class="checkbox" name="45641[64625]" type="checkbox" value="1"> <label id="caption_64625" class="caption" for="field_64625"> 53-62 </label>
<div class="field_error">&nbsp;</div>
</div>
</div>
<div class="field_block" style="display: inline-block; width: 13%;">
<div class="checkbox field"><input id="field_64631" class="checkbox" name="45642[64631]" type="checkbox" value="1"> <label id="caption_64631" class="caption" for="field_64631"> 63-67 </label>
<div class="field_error">&nbsp;</div>
</div>
</div>
<div class="field_block" style="display: inline-block; width: 13%;">
<div class="checkbox field"><input id="field_64632" class="checkbox" name="45643[64632]" type="checkbox" value="1"> <label id="caption_64632" class="caption" for="field_64632"> 68-82 </label>
<div class="field_error">&nbsp;</div>
</div>
</div>
<div class="field_block" style="display: inline-block; width: 13%;">
<div class="checkbox field"><input id="field_64633" class="checkbox" name="45644[64633]" type="checkbox" value="1"> <label id="caption_64633" class="caption" for="field_64633"> 84-96 </label>
<div class="field_error">&nbsp;</div>
</div>
</div>
<div class="field_block" style="display: inline-block; width: 13%;">
<div class="checkbox field"><input id="field_64634" class="checkbox" name="45645[64634]" type="checkbox" value="1"> <label id="caption_64634" class="caption" for="field_64634"> 97-04 </label>
<div class="field_error">&nbsp;</div>
</div>
</div>
<div class="field_block" style="display: inline-block; width: 13%;">
<div class="checkbox field"><input id="field_64635" class="checkbox" name="45646[64635]" type="checkbox" value="1"> <label id="caption_64635" class="caption" for="field_64635"> 05-13 </label>
<div class="field_error">&nbsp;</div>
</div>
</div>
<div class="field_block" style="display: inline-block; width: 13%;">
<div class="checkbox field"><input id="field_213448" class="checkbox" name="45647[213448]" type="checkbox" value="1"> <label id="caption_213448" class="caption" for="field_213448"> C7 </label>
<div class="field_error">&nbsp;</div>
</div>
</div>
</div>
<div id="row_17291" class="section">
<div id="column_21777" style="text-align: left;">
<div class="email field_block">
<div class="field"><input class="text field fb-email" name="45638" size="35" type="text" value="" placeholder="E-mail Address">
<div class="field_error">&nbsp;</div>
</div>
</div>
</div>
</div>
<div id="row_17294" class="section">
<div id="column_21781" style="text-align: left;">
<div class="btn-submit field_block">
<div class="field"><input class="btn btn-primary-2" type="submit" value="Submit"></div>
</div>
<input name="46623[200083]" type="hidden" value="true"></div>
<div style="clear: both;">&nbsp;</div>
</div>
<p class="note">Gift Card Drawing winner will be announced on the fourth Wednesday of the month. winner will be notified by email address at time of sign up.</p>
</form></div>
<div class="col-lg-6"><img src="{{media url=&quot;home/form-advertisment.png&quot;}}" alt="form advertisement"></div>
</div>
<div id="tabs" class="tabs">
<h2>New and Recommended Corvette Parts</h2>
<ul>
        <li><a id="tab1">C1</a></li>
        <li><a id="tab2" class="inactive">C2</a></li>
        <li><a id="tab3" class="inactive">C3</a></li>
        <li><a id="tab4" class="inactive">C4</a></li>
        <li><a id="tab5" class="inactive">C5</a></li>
        <li><a id="tab6" class="inactive">C6</a></li>
        <li><a id="tab7" class="inactive">C7</a></li>
        <li><a id="tab8" class="inactive">Gifts/Accessories</a></li>
    </ul>
<div id="tab1C" class="tab-content">{{block class="Magento\Cms\Block\Block" block_id="c1-home-tab"}}</div>
<div id="tab2C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c2-home-tab"}}</div>
<div id="tab3C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c3-home-tab"}}</div>
<div id="tab4C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c4-home-tab"}}</div>
<div id="tab5C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c5-home-tab"}}</div>
<div id="tab6C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c6-home-tab"}}</div>
<div id="tab7C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c7-home-tab"}}</div>
<div id="tab8C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c7-home-tab"}}</div>
</div>
<div id="tabs2" class="tabs">
<h2>What Other Corvette Owners Are Buying</h2>
<ul>
        <li><a id="tab21">C1</a></li>
        <li><a id="tab22" class="inactive">C2</a></li>
        <li><a id="tab23" class="inactive">C3</a></li>
        <li><a id="tab24" class="inactive">C4</a></li>
        <li><a id="tab25" class="inactive">C5</a></li>
        <li><a id="tab26" class="inactive">C6</a></li>
        <li><a id="tab27" class="inactive">C7</a></li>
        <li><a id="tab28" class="inactive">Gifts/Accessories</a></li>
    </ul>
<div id="tab21C" class="tab-content">{{block class="Magento\Cms\Block\Block" block_id="c1-home-tab"}}</div>
<div id="tab22C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c2-home-tab"}}</div>
<div id="tab23C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c3-home-tab"}}</div>
<div id="tab24C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c4-home-tab"}}</div>
<div id="tab25C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c5-home-tab"}}</div>
<div id="tab26C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c6-home-tab"}}</div>
<div id="tab27C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c7-home-tab"}}</div>
<div id="tab28C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="accessories"}}</div>
</div>
<div class="row advertisment-2">
<div class="homead-1"><a title="power stop" href="https://www.zip-corvette.com/corvette-brands/power-stop.html"><img src="{{media url=&quot;home/two_row/power-stop-left-column-ad-768x455.jpg&quot;}}" alt="power stop"></a></div>
<div class="homead-2"><a title="DeWitts Radiators" href="https://www.zip-corvette.com/corvette-brands/dewitts-radiators.html"><img src="{{media url=&quot;home/two_row/dewitts-radiators-zip-corvette-1080x457.jpg&quot;}}" alt=""></a></div>
</div>
<div class="testimonials">{{block class="Magento\Cms\Block\Block" block_id="testimonial-test-widget"}}</div>
<div class="row advertisment-3">
<div class="col-md-4 col-sm-6 col-xs-12"><a title="acc" href="https://www.zip-corvette.com/corvette-brands/american-car-craft.html"><img src="{{media url=&quot;home/top_row_ads/acc-zip-corvette-612x260.jpg&quot;}}" alt="acc"></a></div>
<div class="col-md-4 col-sm-6 col-xs-12"><a title="lloyds" href="https://www.zip-corvette.com/corvette-brands/lloyd-floor-mats.html"><img src="{{media url=&quot;home/top_row_ads/lloyd-mats-zip-corvette-612x260.jpg&quot;}}" alt="lloyd mats"></a></div>
<div class="col-md-4 col-sm-0 col-xs-12"><a title="afe" href="https://www.zip-corvette.com/corvette-brands/advanced-flow-engineering.html">
<img src="{{media url=&quot;home/top_row_ads/afe_block.jpg&quot;}}" alt="afe power and control" /></a></div>
</div>
<div class="home-bottom-story row row-eq-height">
<div class="col-md-6"><img src="{{media url=&quot;home/About-us-img.png&quot;}}" alt=""></div>
<div class="col-md-6 about-us-home">
<h3><strong>Corvette Parts and Accessories from Zip</strong></h3>
<h4>Real people, selling quality parts, in real time. On the phone and online.</h4>
<p>Zip Corvette is the place to find Corvette parts for sale online. While shopping on our website you have access to over 25,000 parts and accessories, most of which are in stock and ready to ship; plus if you order by 3 PM ET Monday - Friday your order ships that same day. Our FREE full-color <a title="Corvette parts catalogs" href="https://www.zip-corvette.com/catalog/request/form">Corvette parts catalogs</a> are renowned for including the newest-available, most-correct and best quality parts and accessories to be had for every generation. Speaking of generations, we have parts for your C1, C2, C3, C4, C5, C6, and C7 Corvette. Shop our Corvette performance parts to make some noise and add tons of power under the hood or our Corvette restoration parts to roll back the clock and make your Vette resemble its time spent on the Chevrolet dealer showroom floor. If it\'s stamped, cast, forged, milled, molded or stitched, Zip has it!</p>
</div>
</div>

{{widget type="Magento\Cms\Block\Widget\Block" template="widget/static_block/default.phtml" block_id="27" type_name="CMS Static Block"}}';

        $cmspage =  $this->pageFactory->create()->load('home', 'identifier');


        if ($cmspage->getId()) {
            $cmspage->setContent($content)->setTitle('Corvette Parts and Accessories | Zip Corvette')->save();
        } else {
            $cmsData = [
                'title' => 'Corvette Parts and Accessories | Zip Corvette',
                'page_layout' => '1column',
                'identifier' => 'home',
                'content_heading' => '',
                'content' => $content,
                'is_active' => 1,
                'stores' => [0]
            ];

            $this->pageFactory->create()->setData($cmsData)->save();
        }


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
        return '1.0.1';
    }
}