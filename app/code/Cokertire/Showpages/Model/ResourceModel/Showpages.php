<?php

namespace Cokertire\Showpages\Model\ResourceModel;

use Cokertire\Showpages\Model\ResourceModel\Mage_Core_Model_Url_Rewrite;

class Showpages extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        $this->_date = $date;
        parent::__construct($context);
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('showpages', 'showpages_id');
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdateTime($this->_date->gmtDate());
        return parent::_beforeSave($object);
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $identifier = $object->getIdentifier();
        if (!empty($identifier))
        {
            $this->handleUrlRewrite($object);
        }
        return parent::_afterSave($object);
    }

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->deleteUrlRewrites($object);
        return parent::_beforeDelete($object);
    }

    public function getUrl(\Magento\Framework\Model\AbstractModel $object)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        return $_storeManager->getStore()->getUrl(
         'showpages/',
             array(
             'id' => $object->getShowpagesId(),
            )
     );
    }
    public function getUrlRewrite(\Magento\Framework\Model\AbstractModel $object)
    {
        $id_path = "showpages/view/{$object->getShowpagesId()}";
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $mainUrlRewrite = $objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')->getCollection()->addFieldToFilter("target_path",$id_path)->getFirstItem();
        $_storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        if ($mainUrlRewrite->getId())
        {
            return $_storeManager->getUrl($mainUrlRewrite->getRequestPath());
        }
        else
        {
            return $this->getUrl($object);
        }
    }

    protected function handleUrlRewrite(\Magento\Framework\Model\AbstractModel $object)
    {
        $id_path = "showpages/index/view/{$object->getShowpagesId()}";
        $request_path = "shows/{$object->getIdentifier()}";
        $target_path = "showpages/index/view/{$object->getShowpagesId()}";
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
        * First, check if there is already a main url rewrite object.
        */
        /* @var $mainUrlRewrite Mage_Core_Model_Url_Rewrite */
        $mainUrlRewrite = $objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')->getCollection()->addFieldToFilter("target_path",$id_path)->getFirstItem();
        /**
        * If there already is a main url rewrite object, check if there are
        * redirects to this one.
        */
        if (!$mainUrlRewrite->isObjectNew())
        {
            $urlRewriteCollection = $objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')->getCollection()
                ->addFilter('target_path', $mainUrlRewrite->getRequestPath())
                ->addFieldToFilter('url_rewrite_id', array('neq' => $mainUrlRewrite->getUrlRewriteId()))
                ->load();
            /**
            * If there are objects found, those must be redirected to the new
            * request_path.
            */
            foreach ($urlRewriteCollection as $urlRewrite)
            {
                /**
                * Remove those object where the request path equals the current target
                * path. This can occur if the user changes the url key back to
                * an old one.
                */
                if ($urlRewrite->getRequestPath() == $request_path)
                {
                    $urlRewrite->delete();
                }
                else
                {
                    /**
                    * Options are explicitly set to RP and system is set to 1.
                    * This way, the user knows it's changed by the system.
                    */
                    /* @var $urlRewrite Mage_Core_Model_Url_Rewrite */
                    $urlRewrite->setTargetPath($request_path)
                        ->setIsSystem(true)
                        ->setOptions('RP')
                        ->setStoreId(2)
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
            ->setStoreId(2)
            ->save();
        /**
        * Check if a redirect must be made.
        */
        $identifier_create_redirect = $object->getData('identifier_create_redirect');
        if (!empty($identifier_create_redirect))
        {
            /**
            * A permanent redirect to the new url must be made.
            */
            $rewrite = $objectManager->create('Magento\UrlRewrite\Model\UrlRewrite');
            $rewrite->setIdPath("showpages/index/view/{$object->getShowpagesId()}_{$identifier_create_redirect}")
                ->setRequestPath("shows/{$identifier_create_redirect}")
                ->setTargetPath($request_path)
                ->setIsSystem(true)
                ->setOptions('RP')
                ->setStoreId(2)
                ->save();
        }
    }

    protected function deleteUrlRewrites($object)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $id_path = "showpages/index/view/{$object->getShowpagesId()}";
        $mainUrlRewrite = $objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')->getCollection()->addFieldToFilter("target_path",$id_path)->getFirstItem();
        /**
        * If there is a main url rewrite object, check if there are redirects
        * to this one which must be deleted.
        */
        if (!$mainUrlRewrite->isObjectNew())
        {
            $urlRewriteCollection = $objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')->getCollection()
                ->addFilter('target_path', $mainUrlRewrite->getRequestPath())
                ->addFieldToFilter('url_rewrite_id', array('neq' => $mainUrlRewrite->getUrlRewriteId()))
                ->load();
            /**
            * If there are objects found, those must be deleted.
            */
            foreach ($urlRewriteCollection as $urlRewrite)
            {
                $urlRewrite->delete();
            }
        }

        $mainUrlRewrite->delete();
    }



}
