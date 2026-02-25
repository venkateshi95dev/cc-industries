<?php
/**
 * @namespace   Crimson
 * @module      Cms
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        05/21/2019
 */
namespace Crimson\Cms\Setup\Patch\Data;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ChangeTestimonialsToCarrousel implements DataPatchInterface
{
    /**
     * @var BlockCollectionFactory
     */
    private $blockCollectionFactory;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * UpdateTestimonialsContent constructor.
     * @param BlockCollectionFactory $blockCollectionFactory
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        BlockCollectionFactory $blockCollectionFactory,
        BlockFactory $blockFactory
    ) {
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->blockFactory = $blockFactory;
    }

    public function apply()
    {
        $blockFactory = $this->blockCollectionFactory->create();

        $blockContent ='<p>{{widget type="Ves\Testimonial\Block\Widget\CustomWidget" widget_title="What Others Are Saying..." number_item="12" category="1" order_by="rating" layout="style1" grid_column="3" grid_pagination="0" show_name="1" show_address="0" show_email="0" show_company="1" show_job="1" show_title="1" show_image="1" image_width="75" image_height="75" show_rating="0" show_socialnetworks="0" show_date="0" show_readmore="1" readmore_char="400" number_item_percolumn="1" large_max_items="3" large_items="3" portrait_items="3" tablet_items="3" tablet_small_items="1" mobile_items="1" autoplay="0" autoplay_hover_pause="0" dots="1" nav="0" rtl="0" loop="0" type_name="Ves Testimonial Widget"}}</p>';

        $cmsblock = $blockFactory->addFieldToFilter('identifier', 'testimonial-test-widget');
        if (count($cmsblock)) {
            foreach ($cmsblock as $block) {
                $block->setContent($blockContent)->save();
                break;
            }
        } else {
            $cmsBlock = [
                'title' => 'Testimonial Test Widget',
                'identifier' => 'testimonial-test-widget',
                'stores' => [0],
                'content' => $blockContent,
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
            \Crimson\Cms\Setup\Patch\Data\UpdateTestimonialsContent::class
        ];
    }
}
