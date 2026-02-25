<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Ui\DataProvider\Product;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use I95DevConnect\PriceLevel\Model\ResourceModel\ItemPriceListData\CollectionFactory;
use I95DevConnect\PriceLevel\Model\ResourceModel\ItemPriceListData\Collection;

/**
 * Class PricelistDataProvider for UI component
 *
 * @method Collection getCollection
 */
class PricelistDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    public $collectionFactory;

    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var ProductFactory
     */
    public $productloader;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param ProductFactory $productloader
     * @param RedirectInterface $redirect
     * @param array $meta
     * @param array $data
     */
    public function __construct( // NOSONAR
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ProductFactory $productloader,
        RedirectInterface $redirect,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        $this->request = $request;
        $this->productloader = $productloader;
        $this->redirect = $redirect;
    }

    /**
     * Get Price List Data
     *
     * @return array
     */
    public function getData()
    {

        $currentProductId = (int)$this->request->getParam('current_product_id');
        $currentProductId = $currentProductId ? $currentProductId : $this->getParamFromRefferealUrl('id');
        if ($currentProductId) {
            $sku = $this->productloader->create()->load($currentProductId)->getSku();
        } else {
            $sku = $this->coreSession->getProductSku();
        }
        $this->getCollection()->addFieldToSelect('*')->addFieldToFilter('sku', $sku)
            ->setOrder('sales_type', 'ASC')->setOrder('qty', 'ASC');

        $arrItems = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => [],
        ];

        foreach ($this->getCollection() as $item) {
            $arrItems['items'][] = $item->toArray([]);
        }

        return $arrItems;
    }
    /**
     * Get param from refferal url
     *
     * @param string $variableName
     * @param string $default
     * @return mixed|string|null
     */
    private function getParamFromRefferealUrl($variableName, $default = null)
    {
        $url = $this->redirect->getRefererUrl();
        $urlParts = explode('/', preg_replace('/\?.+/', '', $url));
        $position = array_search($variableName, $urlParts);
        if ($position !== false && array_key_exists($position + 1, $urlParts)) {
            return $urlParts[$position + 1];
        }
        return $default;
    }
}
