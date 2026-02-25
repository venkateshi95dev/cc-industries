<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Model\Document;

use Silk\ProductImage\Model\ResourceModel\Document\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    /**
     * @var \Silk\ProductImage\Model\ResourceModel\Document\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $documentCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $documentCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $documentCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {

        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Silk\ProductImage\Model\Document $document */
        foreach ($items as $document) {
            $this->loadedData[$document->getId()] = $this->revertData($document);
        }

        $data = $this->dataPersistor->get('img_document');
        if (!empty($data)) {
            $document = $this->collection->getNewEmptyItem();
            $document->setData($data);
            $this->loadedData[$document->getId()] = $this->revertData($document);
            $this->dataPersistor->clear('img_document');
        }
        return $this->loadedData;
    }
    protected function revertData(&$document){
        $documentData = $document->getData();
        $file_url = $documentData['file']?? '';
        unset($documentData['file']);
        if(!$file_url) return $documentData;
        $documentData['file'][0]['url'] = $file_url;
        $documentData['file'][0]['type'] = $documentData['file_type'];
        $documentData['file'][0]['size'] = $documentData['file_size'];
        $documentData['file'][0]['name'] = $documentData['file_name'];
        $documentData['file'][0]['path'] = $documentData['path'];
        return $documentData;
    }
}
