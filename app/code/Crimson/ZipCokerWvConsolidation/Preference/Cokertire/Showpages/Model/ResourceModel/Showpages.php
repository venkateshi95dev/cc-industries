<?php

namespace Crimson\ZipCokerWvConsolidation\Preference\Cokertire\Showpages\Model\ResourceModel;

use Crimson\CokerWV\Api\CokerStoreInterface;

class Showpages extends \Cokertire\Showpages\Model\ResourceModel\Showpages
{
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime                  $date,
        \Magento\Framework\Model\ResourceModel\Db\Context            $context,
        private readonly \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        private readonly \Magento\Store\Model\StoreManagerInterface  $storeManager
    )
    {
        parent::__construct($date, $context);
    }

    protected function handleUrlRewrite(\Magento\Framework\Model\AbstractModel $object)
    {
        $id_path = "showpages/index/view/{$object->getShowpagesId()}";
        $request_path = "shows/{$object->getIdentifier()}";
        $target_path = "showpages/index/view/{$object->getShowpagesId()}";
        $cokerTireStoreId = $this->storeManager->getStore(CokerStoreInterface::COKER_STORE_CODE)->getId();


        /**
         * First, check if there is already a main url rewrite object.
         */
        $mainUrlRewrite = $this->urlRewriteFactory->create()->getCollection()->addFieldToFilter("target_path", $id_path)->getFirstItem();
        /**
         * If there already is a main url rewrite object, check if there are
         * redirects to this one.
         */
        if (!$mainUrlRewrite->isObjectNew()) {
            $urlRewriteCollection = $this->urlRewriteFactory->create()->getCollection()
                ->addFilter('target_path', $mainUrlRewrite->getRequestPath())
                ->addFieldToFilter('url_rewrite_id', array('neq' => $mainUrlRewrite->getUrlRewriteId()))
                ->load();
            /**
             * If there are objects found, those must be redirected to the new
             * request_path.
             */
            foreach ($urlRewriteCollection as $urlRewrite) {
                /**
                 * Remove those object where the request path equals the current target
                 * path. This can occur if the user changes the url key back to
                 * an old one.
                 */
                if ($urlRewrite->getRequestPath() == $request_path) {
                    $urlRewrite->delete();
                } else {
                    /**
                     * Options are explicitly set to RP and system is set to 1.
                     * This way, the user knows it's changed by the system.
                     */
                    $urlRewrite->setTargetPath($request_path)
                        ->setIsSystem(true)
                        ->setOptions('RP')
                        ->setStoreId($cokerTireStoreId)
                        ->save();
                }
            }
        }
        /**
         * Populate mainUrlRewrite with all data and save it. This way, for new
         * objects, an Url rewrite is created too.
         */
        $mainUrlRewrite->setIdPath($id_path)
            ->setRequestPath($request_path)
            ->setTargetPath($target_path)
            ->setIsSystem(true)
            ->setStoreId($cokerTireStoreId)
            ->save();
        /**
         * Check if a redirect must be made.
         */
        $identifier_create_redirect = $object->getData('identifier_create_redirect');
        if (!empty($identifier_create_redirect)) {
            /**
             * A permanent redirect to the new url must be made.
             */
            $rewrite = $this->urlRewriteFactory->create();
            $rewrite->setIdPath("showpages/index/view/{$object->getShowpagesId()}_{$identifier_create_redirect}")
                ->setRequestPath("shows/{$identifier_create_redirect}")
                ->setTargetPath($request_path)
                ->setIsSystem(true)
                ->setOptions('RP')
                ->setStoreId($cokerTireStoreId)
                ->save();
        }
    }
}
