<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        5/23/2019 12:10 PM
 * @brief
 */
namespace Crimson\Cms\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddDifficultyBlock implements
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
            'install-difficulty-level' => [
                'title' => 'Install Difficulty Level',
                'content' => '<p>Lorem ipsum dolor sit amet, odio aeterno at pro, ei purto homero duo. Solum adolescens has et. Omnium virtute in nam. At his alii possim abhorreant. In ius minim fabulas, velit facilisis vix ex.

Saepe commodo apeirian nec eu. Mel dicit munere intellegat te, ad eos quot sale epicurei, an qui minim error. Id nec decore admodum iudicabit, stet erant per te. Cum an inani mentitum.</p>'
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