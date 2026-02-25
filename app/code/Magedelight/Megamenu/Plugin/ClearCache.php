<?php


namespace Magedelight\Megamenu\Plugin;


use Magedelight\Megamenu\Controller\Adminhtml\Menu\Save;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magedelight\Megamenu\Model\Cache\Type as MegamenuCache;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\PageCache\Model\Cache\Type as PageCache;
use Zend_Cache;

class ClearCache
{
    /**
     * @var StateInterface
     */
    private $cacheState;
    /**
     * @var FrontendPool
     */
    private $cacheFrontendPool;
    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * ClearCache constructor.
     * @param StateInterface $cacheState
     * @param FrontendPool $cacheFrontendPool
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        StateInterface $cacheState,
        FrontendPool $cacheFrontendPool,
        TypeListInterface $cacheTypeList
    ) {
        $this->cacheState = $cacheState;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * @param Save $subject
     * @param $result
     */
    public function afterExecute(Save $subject, $result)
    {
        if ($this->cacheState->isEnabled(MegamenuCache::TYPE_IDENTIFIER)) {
            $cache = $this->cacheFrontendPool->get(MegamenuCache::TYPE_IDENTIFIER);
            $cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [MegamenuCache::CACHE_TAG]);
        }

        // Also clean the full page cache
//        $this->cacheTypeList->cleanType(PageCache::TYPE_IDENTIFIER);
        return $result;
    }
}
