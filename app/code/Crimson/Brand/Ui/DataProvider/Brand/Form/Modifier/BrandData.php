<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/16/2019
 */
namespace Crimson\Brand\Ui\DataProvider\Brand\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Crimson\Brand\Model\ResourceModel\Brand\CollectionFactory;

class BrandData implements ModifierInterface
{
    /**
     * @var \Crimson\Brand\Model\ResourceModel\Brand\Collection
     */
    protected $collection;

    /**
     * @param CollectionFactory $brandCollectionFactory
     */
    public function __construct(
        CollectionFactory $brandCollectionFactory
    ) {
        $this->collection = $brandCollectionFactory->create();
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyData(array $data)
    {
        $items = $this->collection->getItems();
        /** @var $brand \Crimson\Brand\Model\Brand */
        foreach ($items as $brand) {
            $_data = $brand->getData();
            if (isset($_data['large_image'])) {
                $largeImageArr = [];
                $largeImageArr[0]['name'] = $brand->getLargeImage();
                $largeImageArr[0]['url'] = $brand->getLargeImageUrl();
                $_data['large_image'] = $largeImageArr;
            }
            if (isset($_data['small_image'])) {
                $smallImageArr = [];
                $smallImageArr[0]['name'] = $brand->getSmallImage();
                $smallImageArr[0]['url'] = $brand->getSmallImageUrl();
                $_data['small_image'] = $smallImageArr;
            }
            $brand->setData($_data);
            $data[$brand->getId()] = $_data;
        }
        return $data;
    }
}
