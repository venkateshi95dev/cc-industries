<?php

namespace I95DevConnect\Returns\Model\DataPersistence;

use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Model\DataPersistence\Returns\Create;
use I95DevConnect\Returns\Model\DataPersistence\Returns\CreateFactory;
use I95DevConnect\Returns\Model\DataPersistence\Returns\Info;
use I95DevConnect\Returns\Model\DataPersistence\Returns\Response;

class ReturnActions
{
    /**
     *
     * @var Returns\Create
     */
    public $returnCreate;

    /**
     *
     * @var Info
     */
    public $returntInfo;

    /**
     *
     * @var Response
     */
    public $returnResponse;

    /**
     * @var Info
     */
    public $returnInfo;

    /**
     * ReturnActions constructor.
     * @param CreateFactory $returnCreate
     * @param Info $returnInfo
     * @param Response $returnResponse
     */
    public function __construct(
        CreateFactory $returnCreate,
        Info $returnInfo,
        Response $returnResponse
    ) {
        $this->returnCreate = $returnCreate;
        $this->returnInfo = $returnInfo;
        $this->returnResponse = $returnResponse;
    }

    /**
     * Create Product.
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erpCode
     * @return I95DevResponseInterface
     */
    public function create($stringData, $entityCode, $erpCode)
    {
        return $this->returnCreate->create()->createReturn($stringData, $entityCode, $erpCode);
    }

    /**
     * Get Return information
     *
     * @param int $returnId
     * @param string $entityCode
     * @param string $erpCode
     * @return I95DevResponseInterface
     */
    public function getInfo($returnId, $entityCode, $erpCode)
    {
        return $this->returnInfo->getInfo($returnId, $entityCode, $erpCode);
    }

    /**
     * Sets target Return information
     *
     * @param array $requestData
     * @param string $entityCode
     * @param string $erpCode
     * @return I95DevResponseInterface
     */
    public function getResponse($requestData, $entityCode, $erpCode)
    {
        return $this->returnResponse->setResponse($requestData, $entityCode, $erpCode);
    }
}
