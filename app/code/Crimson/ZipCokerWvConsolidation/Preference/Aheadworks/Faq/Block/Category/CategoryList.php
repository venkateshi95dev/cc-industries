<?php

namespace Crimson\ZipCokerWvConsolidation\Preference\Aheadworks\Faq\Block\Category;

use Aheadworks\Faq\Block\Category\CategoryList as AheadworksFaqCategoryList;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Api\SortOrderBuilder;
use Aheadworks\Faq\Model\Url;
use Aheadworks\Faq\Model\Config;
use Aheadworks\Faq\Model\ArticleFactory;
use Aheadworks\Faq\Model\Article;
use Aheadworks\Faq\Api\CategoryRepositoryInterface;
use Aheadworks\Faq\Api\ArticleRepositoryInterface;

/**
 * FAQ Category list
 */
class CategoryList extends AheadworksFaqCategoryList
{

    /**
     * @param Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ArticleRepositoryInterface $articleRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param Url $url
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CategoryRepositoryInterface $categoryRepository,
        ArticleRepositoryInterface $articleRepository,
        SortOrderBuilder $sortOrderBuilder,
        Url $url,
        Config $config,
        private ArticleFactory $articleFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $searchCriteriaBuilder,
            $categoryRepository,
            $articleRepository,
            $sortOrderBuilder,
            $url,
            $config,
            $data
        );
    }
    public function getMost(){
        /** @var Article $articles */
        $articles = $this->articleFactory->create();
        return $articles->getCollection()->setOrder('votes_yes', 'DESC')
                    ->setCurPage(1)
                    ->setPageSize(4);
    }
}
