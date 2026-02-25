<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Product;

use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Create;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Info;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Response;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for creating Product, getting Product info and setting Product response
 */
class Product
{
    /**
     *
     * @var Create
     */
    public $productCreate;

    /**
     *
     * @var Info
     */
    public $productInfo;

    /**
     *
     * @var Response
     */
    public $productResponse;

    /**
     * Product constructor.
     *
     * @param Product\CreateFactory $productCreate
     * @param Product\Info $productInfo
     * @param Product\Response $productResponse
     */
    public function __construct(
        Product\CreateFactory $productCreate,
        Product\Info $productInfo,
        Product\Response $productResponse
    ) {
        $this->productCreate = $productCreate;
        $this->productInfo = $productInfo;
        $this->productResponse = $productResponse;
    }

    /**
     * Create Product.
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erpCode
     *
     * @return I95DevResponseInterface
     */
    public function create($stringData, $entityCode, $erpCode)
    {
        return $this->productCreate->create()->createProduct($stringData, $entityCode, $erpCode);
    }

    /**
     * Get Product information
     *
     * @param int $productId
     * @param string $entityCode
     * @param string $erpCode
     * @return array
     * @throws LocalizedException
     */
    public function getInfo($productId, $entityCode, $erpCode) //NOSONAR
    {
        return  $this->productInfo->getInfo($productId);
    }

    /**
     * Sets target Product information
     *
     * @param array $requestData
     * @param string $entityCode
     * @param string $erpCode
     *
     * @return AbstractDataPersistence
     */
    public function getResponse($requestData, $entityCode, $erpCode) //NOSONAR
    {
        return $this->productResponse->setProductResponse($requestData);
    }
}
