<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        5/17/2019 11:34 AM
 * @brief
 */
namespace Crimson\Cms\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;

class UpdateHeaderLinks implements
    DataPatchInterface,
    PatchVersionInterface
{

    /**
     * Block factory.
     *
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    private $blockCollectionFactory;

    /**
     * Block factory.
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;

    /*
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /*
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    private $blockRepository;

    public function __construct(
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
    )
    {
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->blockFactory = $blockFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->blockRepository = $blockRepository;
    }

    public function apply()
    {

        $dataBlocks =array(
            'top-header-links' => array(
                'title' => 'Top Header Links',
                'content' => '<ul class="top-header-links">
<li><a href="{{store url=\'\'}}our-story.html">Since 1977</a></li>
<li><a href="{{store url=\'\'}}crimson_mach">Request a free parts catalog</a></li>
<li class="hours-modal"><a class="hours-modal-link" href="#">Hours of Operation</a>
<div class="hours-modal-content">
<h3>Call Center Hours</h3>
  <p>Zip Corvette sales and customer service representatives are here to serve you during the following hours:</p>
  <ul class="unstyled">
    <li>Mon-Thu. 8:30am-8pm</li>
    <li>Friday 8:30am-5:30pm</li>
    <li>Saturday 10am-3pm</li>
  </ul>
  <p>(Eastern Standard Time)</p>
  <hr />
  <p>Our showroom is open to the public Monday-Friday from 8:30am-5:30pm (EST). We take orders in our showroom or you may call your order in over the phone and we will prepare your items in advance so you do not have to wait.</p>
  <hr />
  <p>Our performance and installation facility is open Monday-Friday from 8:30am-5:30pm (EST). Visit our showroom to setup an appointment to have your Corvette tuned, accessorized, enhanced or more. Read more about our <a title="Zip Corvette Installs" href="/we-install-corvette-parts">performance and installation facility here</a>. *All work requires an appointment.</p>
  <p> Other questions? <a title="Contact Us" href="/contacts">Contact us here</a>&nbsp;24 hours a day 7 days a week.</p>
</div>
</li>
<li><a href="https://www.corvettemagazine.com/" target="_blank" rel="noopener">Tech Articles</a></li>
<li><a href="{{store url=\'\'}}accessories/corvette-gift-cards.html">Gift Cards</a></li>
<li><a href="{{store url=\'\'}}new-parts-and-accessories.html">NEW Items</a></li>
<li class="sub-banner-2">
<div class="dropdown phone" data-toggle="popover">We are currently open <a href="#&quot;"><span class="number">1.800.962.9632 </span></a> <span id="header-contact" data-trigger="click" data-placement="bottom" data-html="true" data-title="We are here to help." data-content="<ul class=\'header-contact-popdown\'>
                           <li><i class=\'fa fa-phone-square fa-2x\'></i> 1-800-962-9632</li>
                           <li><i class=\'fa fa-envelope-square fa-2x\'></i> <a href=\'https://secure.livechatinc.com/licence/4282571/open_chat.cgi\' title=\'Email Us\' target=\'_blank\'>Email</a></li>
                           <li><i class=\'fa fa-keyboard-o fa-2x\'></i> <a href=\'https://secure.livechatinc.com/licence/4282571/open_chat.cgi\' title=\'Live Chat\' target=\'_blank\'>Live Chat</a></li>
                       </ul>"></span></div>
</li>
</ul>'
            ),
            'footer-links' => array(
                'title' => 'Footer Links',
                'content' => '<div class="footer-inner-top-row clearfix">
<div class="row-fluid row">
<div class="span4 col-lg-4">
<div class="about-brief">
<div class="row-fluid row">
<div class="span5 catalog-img col-sm-7"><a href="{{store url=\'catalog/request/form\'}}"><img src="https://s3.amazonaws.com/zip-corvette/web/catalogs/catalog-footer-img.png"></a>
<h4><a href="{{store url=\'catalog/request/form\'}}">Get Our free Catalog today!</a></h4>
<p>Our Corvette Parts &amp; Accessories Catalogs have guided thousands of customers through detailed restorations, enhancements and performance modifications.</p>
</div>
<div class="span7 col-sm-5">
<h4>Contact Us</h4>
<ul class="unstyled">
<li>Zip Products Inc.</li>
<li><span class="smaller">8067 Fast Lane Mechanicsville, VA 23111</span></li>
</ul>
<div class="hours">
<h4 class="hours-modal">Hours of Operation</h4>
<div class="hours-modal-content">
<h3>Call Center Hours</h3>
<p>Zip Corvette sales and customer service representatives are here to serve you during the following hours:</p>
<ul class="unstyled">
<li>Mon-Thu. 8:30am-8pm</li>
<li>Friday 8:30am-5:30pm</li>
<li>Saturday 10am-3pm</li>
</ul>
<p>(Eastern Standard Time)</p>
<hr>
<p>Our showroom is open to the public Monday-Friday from 8:30am-5:30pm (EST). We take orders in our showroom or you may call your order in over the phone and we will prepare your items in advance so you do not have to wait.</p>
<hr>
<p>Our performance and installation facility is open Monday-Friday from 8:30am-5:30pm (EST). Visit our showroom to setup an appointment to have your Corvette tuned, accessorized, enhanced or more. Read more about our <a title="Zip Corvette Installs" href="/we-install-corvette-parts">performance and installation facility here</a>. *All work requires an appointment.</p>
<p>Other questions? <a title="Contact Us" href="/contacts">Contact us here</a>&nbsp;24 hours a day 7 days a week.</p>
</div>
<span class="smaller">Monday-Friday: 8:00 am - 5:00pm <br>Closed on weekends and holidays.</span></div>
<div class="contact">
<h4>Need Help Now?</h4>
<p>1-800-962-9632 or</p>
<p>&nbsp;</p>
</div>
</div>
</div>
</div>
</div>
<div class="span8 col-lg-8">
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
<li><a href="https://www.corvettemagazine.com/" target="_blank" rel="noopener">Technical Articles</a></li>
<li></li>
</ul>
</div>
</div>
</div>
</div>
</div>
<div class="footer-inner-bottom"><a href="https://sealserver.trustwave.com/cert.php?customerId=&amp;size=78x40&amp;style=normal&amp;baseURL=www.zip-corvette.com" target="_blank" rel="noopener"><img id="footer_trust" src="{{media url=&quot;tc-seal-white.png&quot;}}" alt="This site protected by Trustwave\'s Trusted Commerce program" border="0"></a> <a href="https://www.mcafeesecure.com/verify?host=www.zip-corvette.com" target="_blank" rel="noopener"><img class="mfes-trustmark mfes-trustmark-hover" title="McAfee SECURE sites help keep you safe from identity theft, credit card fraud, spyware, spam, viruses and online scams" src="//cdn.ywxi.net/meter/www.zip-corvette.com/101.gif" alt="McAfee SECURE sites help keep you safe from identity theft, credit card fraud, spyware, spam, viruses and online scams" width="125" height="55" border="0"></a> <img src="{{media url=&quot;armo.png&quot;}}" alt=""> <img src="{{media url=&quot;sbn.png&quot;}}" alt=""></div>'
            )
        );


        foreach ($dataBlocks as $id => $dataBlock) {
            $search = $this->searchCriteriaBuilder->addFilter('identifier', $id, 'eq')->create();
            $cmsblock = $this->blockRepository->getList($search)->getItems();
            if (count($cmsblock)) {

                reset($cmsblock);
                $cms = $cmsblock[key($cmsblock)];
                $cms->setContent($dataBlock['content']);

                $this->blockRepository->save($cms);

            } else {
                $cmsBlock = [
                    'title' => $dataBlock['title'],
                    'identifier' => $id,
                    'stores' => [0],
                    'content' => $dataBlock['content'],
                    'is_active' => 1,
                ];
                $cms = $this->blockFactory->create()->setData($cmsBlock);
                $this->blockRepository->save($cms);
            }
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