<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        3/11/2019 2:44 PM
 * @brief
 */
namespace Crimson\Cms\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;

class AddHomePage implements
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
    <div class="item"><img src="{{media url=&quot;home/Untitled-1.png&quot;}}" alt="Slider 1"></div>
    <div class="item"><img src="{{media url=&quot;home/Untitled-1.png&quot;}}" alt="Slider 2"></div>
</div>
<div class="top-section">
    <h2>Corvette Parts and Accessories by Zip. Corvettes are all we do!</h2>
    <h2><strong>Need Help? 1-800-962-9632</strong></h2>
</div>
<div class="row advertisment-1">&nbsp;</div>
<div class="row form row-eq-height"><!-- form -->
    <div class="col-sm-6 newsletter-form">
        <h3><strong>Our Newsletter</strong></h3>
        <p>Sign up and you\'ll be automatically entered to win a <strong>$50 gift card</strong> plus we\'ll send you subscriber only discounts and more.</p>
        <br> <br><form action="//email.zip-corvette.com/public/webform/process/" method="post"><input name="fid" type="hidden" value="41vrbeqb6z4zxjemu5clxev2uwgz6"> <input name="sid" type="hidden" value="2cdb8ef4bb49bd5a3a5c35c299d7a019"> <input name="delid" type="hidden" value=""> <input name="subid" type="hidden" value=""> <input name="td" type="hidden" value=""> <input name="formtype" type="hidden" value="addcontact">
            <div id="row_17292" class="section">
                <div id="column_21778" style="text-align: left;">
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
                <div style="clear: both;">&nbsp;</div>
            </div>
            <div id="row_17291" class="section">
                <div id="column_21777" style="text-align: left;">
                    <div class="email field_block">
                        <div class="caption">Email Address <span class="required">*</span></div>
                        <div class="field"><input class="text field fb-email" name="45638" size="35" type="text" value="">
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
        </form></div>
    <div class="col-sm-6"><img src="{{media url=&quot;home/form-advertisment.png&quot;}}" alt="form advertisement"></div>
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
    </ul>
    <div id="tab1C" class="tab-content">{{block class="Magento\Cms\Block\Block" block_id="c1-home-tab"}}</div>
    <div id="tab2C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c2-home-tab"}}</div>
    <div id="tab3C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c3-home-tab"}}</div>
    <div id="tab4C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c4-home-tab"}}</div>
    <div id="tab5C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c5-home-tab"}}</div>
    <div id="tab6C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c6-home-tab"}}</div>
    <div id="tab7C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c7-home-tab"}}</div>
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
    </ul>
    <div id="tab21C" class="tab-content">{{block class="Magento\Cms\Block\Block" block_id="c1-home-tab"}}</div>
    <div id="tab22C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c2-home-tab"}}</div>
    <div id="tab23C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c3-home-tab"}}</div>
    <div id="tab24C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c4-home-tab"}}</div>
    <div id="tab25C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c5-home-tab"}}</div>
    <div id="tab26C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c6-home-tab"}}</div>
    <div id="tab27C" class="tab-content" style="display: none;">{{block class="Magento\Cms\Block\Block" block_id="c7-home-tab"}}</div>
</div>
<div class="row advertisment-2">
    <div class="homead-1"><img src="{{media url=&quot;home/ad-2-1.png&quot;}}" alt=""></div>
    <div class="homead-2"><img src="{{media url=&quot;home/ad-2-2.png&quot;}}" alt=""></div>
</div>
<div class="row advertisment-3">
    <div class="col-sm-4"><img src="{{media url=&quot;home/ad-3-1.png&quot;}}" alt=""></div>
    <div class="col-sm-4"><img src="{{media url=&quot;home/ad-3-2.png&quot;}}" alt=""></div>
    <div class="col-sm-4"><img src="{{media url=&quot;home/ad-3-3.png&quot;}}" alt=""></div>
</div>
<div class="home-bottom-story row row-eq-height">
    <div class="col-sm-6"><img src="{{media url=&quot;home/About-us-img.png&quot;}}" alt=""></div>
    <div class="col-sm-6 about-us-home">
        <h3><strong>The Zip Story</strong></h3>
        <h4>Zip Products is real people, selling real Corvette Parts, in real time. On the phone and online.</h4>
        <p>Our FREE full-color are renowned for including the newest-available, most-correct and best quality Corvette Parts to be had. Zip\'s web store offers much more than "anytime" access, it\'s a veritable gold mine of photos, expanded part descriptions, helpful assembly illustrations, how-to install guidance and Corvette accessories. Plus, in-stock status is shown for every item - dial or click with confidence! If it\'s stamped, cast, forged, milled, molded or stitched, Zip Corvette has it!</p>
    </div>
</div>

<script type="text/javascript">
    require([
                \'jquery\',
                \'owlcarouselslider\'
            ], function ($) {
        $(".home-slider").owlCarousel({
                                          items: 1,
                                          pagination: false,
                                          nav:true,
                                          dots: true,
                                          navText:	[\'<i class="fa fa-angle-left" aria-hidden="true"></i>\',\'<i class="fa fa-angle-right" aria-hidden="true"></i>\'],
                                          responsiveClass:true,

                                      });
        $(".product-items").owlCarousel({
                                            items: 5,
                                            pagination: false,
                                            nav:true,
                                            dots:false,
                                            stagePadding:70,
                                            navText:	[\'<i class="fa fa-angle-left" aria-hidden="true"></i>\',\'<i class="fa fa-angle-right" aria-hidden="true"></i>\'],
                                            responsiveClass:true,
                                            responsive:{
                                                0:{items:1},
                                                426:{items:2},
                                                768:{items:3},
                                                1024:{items:4},
                                                1281:{items:5}
                                            }
                                        });
        $(\'#tabs li a\').click(function(){
            var t = $(this).attr(\'id\');

            if($(this).hasClass(\'inactive\')){
                $(\'#tabs li a\').addClass(\'inactive\');
                $(this).removeClass(\'inactive\');

                $(\'#tabs .tab-content\').hide();
                $(\'#\'+ t + \'C\').fadeIn(\'slow\');
            }
            $(\'#tabs2 li a\').click(function(){
                var t = $(this).attr(\'id\');

                if($(this).hasClass(\'inactive\')){
                    $(\'#tabs2 li a\').addClass(\'inactive\');
                    $(this).removeClass(\'inactive\');

                    $(\'#tabs2 .tab-content\').hide();
                    $(\'#\'+ t + \'C\').fadeIn(\'slow\');
                }
            });
        });

    });
</script>';

        $cmspage =  $this->pageFactory->create()->load('home', 'identifier');
        $blockFactory = $this->blockCollectionFactory->create();


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



        $blocksData = array(
            'c1-home-tab' => array(
                'title' => 'C1 Home Tab',
                'content' => '<p>{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="10" template="Magento_CatalogWidget::product/widget/content/grid.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`category_ids`,`operator`:`==`,`value`:`3`^]^]" type_name="Catalog Products List"}}</p>'
            ),
            'c2-home-tab' => array(
                'title' => 'C2 Home Tab',
                'content' => '<p>{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="10" template="Magento_CatalogWidget::product/widget/content/grid.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`category_ids`,`operator`:`==`,`value`:`4`^]^]" type_name="Catalog Products List"}}</p>'
            ),
            'c3-home-tab' => array(
                'title' => 'C3 Home Tab',
                'content' => '<p>{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="10" template="Magento_CatalogWidget::product/widget/content/grid.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`category_ids`,`operator`:`==`,`value`:`5`^]^]" type_name="Catalog Products List"}}</p>'
            ),
            'c4-home-tab' => array(
                'title' => 'C4 Home Tab',
                'content' => '<p>{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="10" template="Magento_CatalogWidget::product/widget/content/grid.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`category_ids`,`operator`:`==`,`value`:`6`^]^]" type_name="Catalog Products List"}}</p>'
            ),
            'c5-home-tab' => array(
                'title' => 'C5 Home Tab',
                'content' => '<p>{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="10" template="Magento_CatalogWidget::product/widget/content/grid.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`category_ids`,`operator`:`==`,`value`:`7`^]^]" type_name="Catalog Products List"}}</p>'
            ),
            'c6-home-tab' => array(
                'title' => 'C6 Home Tab',
                'content' => '<p>{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="10" template="Magento_CatalogWidget::product/widget/content/grid.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`category_ids`,`operator`:`==`,`value`:`8`^]^]" type_name="Catalog Products List"}}</p>'
            ),
            'c7-home-tab' => array(
                'title' => 'C7 Home Tab',
                'content' => '<p>{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="10" template="Magento_CatalogWidget::product/widget/content/grid.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`category_ids`,`operator`:`==`,`value`:`9`^]^]" type_name="Catalog Products List"}}</p>'
            ),
            'accessories' => array(
                'title' => 'Accessories',
                'content' => '<p>{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="10" template="Magento_CatalogWidget::product/widget/content/grid.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`category_ids`,`operator`:`==`,`value`:`10`^]^]" type_name="Catalog Products List"}}</p>'
            )
        );

        foreach ($blocksData as $id => $data) {
            $cmsblock = $blockFactory->addFieldToFilter('identifier', $id);
            if (count($cmsblock)) {
                foreach ($cmsblock as $block) {
                    $block->setContent($data['content'])->save();
                    break;
                }
            } else {
                $cmsBlock = [
                    'title' => $data['title'],
                    'identifier' => $id,
                    'stores' => [0],
                    'content' => $data['content'],
                    'is_active' => 1,
                ];
                $this->blockFactory->create()->setData($cmsBlock)->save();
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