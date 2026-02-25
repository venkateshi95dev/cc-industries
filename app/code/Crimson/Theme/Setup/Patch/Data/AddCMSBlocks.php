<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/8/2019 2:57 PM
 * @brief
 */
namespace Crimson\Theme\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;

class AddCMSBlocks implements
    DataPatchInterface,
    PatchVersionInterface
{
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
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory
    )
    {
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->blockFactory = $blockFactory;
    }

    public function apply()
    {
        $blockFactory = $this->blockCollectionFactory->create();

        $content ='<ul class="top-header-links">
<li><a href="#">Since 1977</a></li>
<li><a href="#">Request a free parts catalog</a></li>
<li><a href="#">Hours of Operation</a></li>
<li><a href="#">Tech Articles</a></li>
<li><a href="#">Blog</a></li>
<li><a href="#">Gift Cards</a></li>
<li><a href="#">NEW Items</a></li>
<li class="sub-banner-2">
<div class="dropdown phone" data-toggle="popover"><i class="fa fa-phone-square"></i>We are currently open <span class="number">1.800.962.9632 <i class="fa fa-caret-down"></i></span> 
<span id="header-contact" data-trigger="click" data-placement="bottom" data-html="true" data-title="We are here to help." data-content="<ul class=\'header-contact-popdown\'>
                            <li><i class=\'fa fa-phone-square fa-2x\'></i> 1-800-962-9632</li>
                            <li><i class=\'fa fa-envelope-square fa-2x\'></i> <a href=\'https://secure.livechatinc.com/licence/4282571/open_chat.cgi\' title=\'Email Us\' target=\'_blank\'>Email</a></li>
                            <li><i class=\'fa fa-keyboard-o fa-2x\'></i> <a href=\'https://secure.livechatinc.com/licence/4282571/open_chat.cgi\' title=\'Live Chat\' target=\'_blank\'>Live Chat</a></li>
                        </ul>"></span></div>
</li>
</ul>';

        $cmsblock = $blockFactory->addFieldToFilter('identifier', 'top-header-links');

        if ($cmsblock) {
            foreach ($cmsblock as $block) {
                $block->setContent($content)->save();
                break;
            }
        } else {
            $cmsBlock = [
                'title' => 'Top Header Links',
                'identifier' => 'top-header-links',
                'stores' => [0],
                'content' => $content,
                'is_active' => 1,
            ];
            $this->blockFactory->create()->setData($cmsBlock)->save();
        }

        $content ='<div class="footer-inner-top-row clearfix">
<div class="row-fluid row">
<div class="span4 col-sm-4">
<div class="about-brief">
<div class="row-fluid row">
<div class="span5 catalog-img col-sm-6"><a href="/crimson_mach"><img src="https://s3.amazonaws.com/zip-corvette/web/catalogs/catalog-footer-img.png"></a>
<p>Our Corvette Parts &amp; Accessories Catalogs have guided thousands of customers through detailed restorations, enhancements and performance modifications.</p>
<a href="/crimson_mach">Get your free copy today!</a></div>
<div class="span7 col-sm-6">
<h4>Contact Us</h4>
<ul class="unstyled">
<li>Zip Products Inc.</li>
<li><span class="smaller">8067 Fast Lane Mechanicsville, VA 23111</span></li>
</ul>
<div class="hours">
<h4>Hours of Operation</h4>
<span class="smaller">Monday-Friday: 8:00 am - 5:00pm <br>Closed on weekends and holidays.</span></div>
<div class="contact">
<h4>Need Help Now?</h4>
<p>1-800-962-9632 or</p>
</div>
</div>
</div>
</div>
</div>
<div class="span8 col-sm-8">
<div class="row-fluid row">&nbsp;</div>
<div class="row-fluid row">
<div class="span3 col-sm-3">
<h4>Your Account</h4>
<ul class="unstyled">
<li><a href="{{store url=\'\'}}">Account Home</a></li>
<li><a href="{{store url=\'\'}}">View Shopping Cart</a></li>
<li><a href="{{store url=\'\'}}">View Wish List</a></li>
<li><a href="{{store url=\'\'}}">View Order History</a></li>
<li><a href="{{store url=\'\'}}">Login</a></li>
</ul>
</div>
<div class="span3 col-sm-3">
<h4>Need Help?</h4>
<ul class="unstyled">
<li><a href="{{store url=\'\'}}">Sales Tax FAQ</a></li>
<li><a href="{{store url=\'\'}}">Ordering Methods</a></li>
<li><a href="{{store url=\'\'}}">Shipping</a></li>
<li><a href="{{store url=\'\'}}">Discount Programs</a></li>
<li><a href="{{store url=\'\'}}">Returns &amp; Exchanges</a></li>
<li><a href="{{store url=\'\'}}">Sitemap</a></li>
</ul>
</div>
<div class="span3 col-sm-3">
<h4>About Zip Products</h4>
<ul class="unstyled">
<li><a href="https://www.youtube.com/user/ZipCorvette?feature=mhum">Watch Our Video</a></li>
<li><a href="{{store url=\'\'}}">Our Guarantee</a></li>
<li><a href="{{store url=\'\'}}">Customer Testimonials</a></li>
<li><a href="{{store url=\'\'}}">We Install</a></li>
<li><a href="{{store url=\'\'}}">Privacy Policy</a></li>
<li><a href="{{store url=\'\'}}">Liability Disclaimer</a></li>
</ul>
</div>
<div class="span3 col-sm-3">
<h4>Connect</h4>
<ul class="unstyled">
<li><a href="https://www.facebook.com/ZipCorvetteParts" target="_blank" rel="noopener">Facebook</a></li>
<li><a href="https://twitter.com/#!/Corvette_Parts" target="_blank" rel="noopener">Twitter</a></li>
<li><a href="https://www.youtube.com/user/ZipCorvette?feature=mhum" target="_blank" rel="noopener">Youtube</a></li>
<li><a href="https://plus.google.com/117242916203293033004/" target="_blank" rel="noopener">Google+</a></li>
<li><a href="https://www.corvettemagazine.com/" target="_blank" rel="noopener">Technical Articles</a></li>
<li><a href="https://www.zip-corvette.com/blog" target="_blank" rel="noopener">Corvette Parts Blog</a></li>
</ul>
</div>
</div>
</div>
</div>
</div>
<div class="footer-inner-bottom"><a href="https://sealserver.trustwave.com/cert.php?customerId=&amp;size=78x40&amp;style=normal&amp;baseURL=www.zip-corvette.com" target="_blank" rel="noopener"><img id="footer_trust" src="{{view url=&quot;images/tc-seal-white.png&quot;}}" alt="This site protected by Trustwave\'s Trusted Commerce program" border="0"></a> <a href="https://www.mcafeesecure.com/verify?host=www.zip-corvette.com" target="_blank" rel="noopener"><img class="mfes-trustmark mfes-trustmark-hover" title="McAfee SECURE sites help keep you safe from identity theft, credit card fraud, spyware, spam, viruses and online scams" src="//cdn.ywxi.net/meter/www.zip-corvette.com/101.gif" alt="McAfee SECURE sites help keep you safe from identity theft, credit card fraud, spyware, spam, viruses and online scams" width="125" height="55" border="0"></a></div>';

        $cmsblock = $blockFactory->addFieldToFilter('identifier', 'footer-links');

        if ($cmsblock) {
            foreach ($cmsblock as $block) {
                $block->setContent($content)->save();
                break;
            }
        } else {
            $cmsBlock = [
                'title' => 'Footer Links',
                'identifier' => 'footer-links',
                'stores' => [0],
                'content' => $content,
                'is_active' => 1,
            ];
            $this->blockFactory->create()->setData($cmsBlock)->save();
        }

        $content ='<div class="row">
<div class="col-sm-6">
<div class="col-sm-4 first-column">
<ul class="menu-column">
<li><a href="#">Collections</a></li>
<li><a href="#">Gift Guide on Models</a></li>
<li><a href="#">New Arrivals</a></li>
<li><a href="#">ACI Fiberglass</a></li>
<li><a href="#">Auto Custom Carpets</a></li>
<li><a href="#">Phoenix Graphics</a></li>
<li><a href="#">Trim Parts</a></li>
</ul>
</div>
<div class="col-sm-4">
<ul class="menu-column">
<li><a href="#">Body &amp; Fiberglass</a></li>
<li><a href="#">Brakes</a></li>
<li><a href="#">Car Covers</a></li>
<li><a href="#">Cooling System</a></li>
<li><a href="#">Eletrical System</a></li>
<li><a href="#">Engine</a></li>
<li><a href="#">Exhaust</a></li>
<li><a href="#">Exterior Trim &amp; Glass</a></li>
<li><a href="#">Frame &amp; Chassis</a></li>
<li><a href="#">Fuel System</a></li>
</ul>
</div>
<div class="col-sm-4">
<ul class="menu-column">
<li><a href="#">Hardtop &amp; Convertible</a></li>
<li><a href="#">Headlights &amp; Lamps</a></li>
<li><a href="#">Heater</a></li>
<li><a href="#">Interior</a></li>
<li><a href="#">Restoration Supplies</a></li>
<li><a href="#">Shifter &amp; Transmission</a></li>
<li><a href="#">Steering Systems</a></li>
<li><a href="#">Suspension &amp; Driveline</a></li>
<li><a href="#">Weatherstrip</a></li>
<li><a href="#">Wheels &amp; Tires</a></li>
</ul>
</div>
</div>
<div class="col-sm-6">
<div class="col-sm-6"><img src="{{media url=\'menu/menu-1.png\'}}" alt="">
<p>Corvette ZR1 212-MPH, 755-HP</p>
</div>
<div class="col-sm-6"><img src="{{media url=\'menu/menu-2.png\'}}" alt="">
<p>2011 Corvette Z06 - Goodyear Eagle F1 Tires</p>
</div>
</div>
</div>';

        $cmsblock = $blockFactory->addFieldToFilter('identifier', 'megamenu_1');

        if ($cmsblock) {
            foreach ($cmsblock as $block) {
                $block->setContent($content)->save();
                break;
            }
        } else {
            $cmsBlock = [
                'title' => 'MegaMenu 1st Dropdown',
                'identifier' => 'megamenu_1',
                'stores' => [0],
                'content' => $content,
                'is_active' => 1,
            ];
            $this->blockFactory->create()->setData($cmsBlock)->save();
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