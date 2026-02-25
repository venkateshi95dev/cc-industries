<?php /** @noinspection PhpIllegalStringOffsetInspection */

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model\DataPersistence;

use Exception;
use I95DevConnect\DiscountGroups\Model\CustomerdiscountgroupFactory;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\ServiceRequest;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use I95DevConnect\MessageQueue\Model\Logger;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class CustomerDiscountGroupSync
{
    /**
     * @var ServiceRequest
     */
    private $requestHelper;

    /**
     * @var I95DevResponseInterface
     */
    public $i95DevResponse;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var Validate
     */
    public $validate;

    /**
     * @var string[]
     */
    public $validateFields = [
        'targetId' => 'Customer Discount Group is required'
        ];

    /**
     * @var CustomerdiscountgroupFactory
     */
    protected $customerdiscountgroupModel;

    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     * CustomerDiscountGroupSync constructor
     *
     * @param Data $dataHelper
     * @param I95DevResponseInterface $i95DevResponse
     * @param Logger $logger
     * @param ServiceRequest $requestHelper
     * @param Manager $eventManager
     * @param Validate $validate
     * @param CustomerdiscountgroupFactory $customerdiscountgroupModel
     * @param DateTime $date
     * @param AbstractDataPersistence $abstractDataPersistence
     */
    public function __construct(
        Data $dataHelper,
        I95DevResponseInterface $i95DevResponse,
        Logger $logger,
        ServiceRequest $requestHelper,
        Manager $eventManager,
        Validate $validate,
        CustomerdiscountgroupFactory $customerdiscountgroupModel,
        DateTime $date,
        AbstractDataPersistence $abstractDataPersistence
    ) {
        $this->i95DevResponse = $i95DevResponse;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->requestHelper = $requestHelper;
        $this->eventManager = $eventManager;
        $this->validate = $validate;
        $this->customerdiscountgroupModel = $customerdiscountgroupModel;
        $this->date = $date;
        $this->abstractDataPersistence = $abstractDataPersistence;
    }

    /**
     * Sync customer discount group codes
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erp
     * @return I95DevResponseInterface
     * @throws LocalizedException
     */
    public function create($stringData, $entityCode, $erp)
    {
        try {
            $this->validate->validateFields = $this->validateFields;
            if (!$this->validate->validateData($stringData)) {
                return $this->returnResponse();
            } else {
                $code = $stringData['targetId'];
                $cdgData = $this->customerdiscountgroupModel->create()->getCollection()
                ->addFieldtoFilter('cdg_code', $code)
                ->getData();
                if (empty($cdgData)) {
                    $cdg = $this->customerdiscountgroupModel->create();
                    $cdg->setCdgCode($code);
                    $cdg->setCreatedDt($this->date->gmtDate());
                } else {
                    $cdgId = $cdgData[0]['id'];
                    $cdg = $this->customerdiscountgroupModel->create()->load($cdgId);
                }
                $cdg->setCdgCode($code);
                /** @noinspection PhpIllegalStringOffsetInspection */
                $cdg->setCdgDescription($stringData['description']);
                $cdg->setUpdatedDt($this->date->gmtDate());
                $cdg->Save();
                $magentoId = $cdg->getId();
            }

            // phpcs:disable
            $this->dataHelper->unsetGlobalValue('i95_observer_skip');
            $jsondata = json_encode(["entityCode" => $entityCode,
            "targetId" => $code,
            "source" => "ERP"]);

            $this->dataHelper->coreRegistry->unregister('savingSource');
            $this->dataHelper->coreRegistry->register('savingSource', $jsondata);

            $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_' . $entityCode;
            $this->eventManager->dispatch($aftereventname, ['currentObject' => $this]);
            return $this->abstractDataPersistence->setResponse(
                Data::SUCCESS,
                "Record Successfully Synced",
                $magentoId
            );
            // phpcs:enable
        } catch (Exception $ex) {
            $this->logger->createLog(
                '__METHOD__',
                $ex->getMessage() . $erp,
                LoggerInterface::I95EXC,
                'error'
            );

            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __('There was an error while syncing data to magento.'),
                null
            );
        }
    }
}
