<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        5/20/2019 1:51 PM
 * @brief
 */
namespace Crimson\Cms\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ATypicalRegionMessageBlock implements
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
        $dataBlocks = [
            'shipping-region-message' => [
                'title' => 'Shipping Region Message',
                'content' => 'We are unable to calculate shipping charges for the ship to address you have specified because it does not fall within the contiguous 48 United States. Your initial ship charge will show $0, however we will calculate all associated shipping fees after receipt of your order. Prior to shipping your order a representative will contact you with the total amount for approval. Need help? 

<a href="{{store url=\'\'}}shipping">View our shipping policies</a> or <a href="{{store url=\'\'}}contact-us">contact us here</a>.'
            ]
        ];

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