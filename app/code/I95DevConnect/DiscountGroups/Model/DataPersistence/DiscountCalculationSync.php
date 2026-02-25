<?php /** @noinspection ALL */

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model\DataPersistence;

use Exception;
use I95DevConnect\DiscountGroups\Helper\Data as Helper;
use I95DevConnect\DiscountGroups\Model\DiscountcalculationFactory;
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

class DiscountCalculationSync
{
    /**
     * @var ServiceRequest
     */
    private $requestHelper;

    /**
     * @var array
     */
    public $postData;

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
        'targetId' => 'Required Item Discount Group Code',
        'qty' => 'Required Quantity',
        'price' => 'Required Price'
        ];

    /**
     * @var DiscountcalculationFactory
     */
    protected $discountcalculationModel;

    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     * Discount Calculation Sync constructor
     *
     * @param Data $dataHelper
     * @param I95DevResponseInterface $i95DevResponse
     * @param Logger $logger
     * @param ServiceRequest $requestHelper
     * @param Manager $eventManager
     * @param Validate $validate
     * @param DiscountcalculationFactory $discountcalculationModel
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
        DiscountcalculationFactory $discountcalculationModel,
        DateTime $date,
        AbstractDataPersistence $abstractDataPersistence
    ) {
        $this->i95DevResponse = $i95DevResponse;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->requestHelper = $requestHelper;
        $this->eventManager = $eventManager;
        $this->validate = $validate;
        $this->discountcalculationModel = $discountcalculationModel;
        $this->date = $date;
        $this->abstractDataPersistence = $abstractDataPersistence;
    }

    /**
     * Sync discount calculations
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erp
     * @return I95DevResponseInterface
     * @throws LocalizedException
     * @noinspection PhpWrongForeachArgumentTypeInspection
     */
    public function create($stringData, $entityCode, $erp)
    {
        try {
            $this->deleteExistingData($stringData);

            foreach ($stringData['discountPrices'] as $discountPrice) {
                $this->validate->validateFields = $this->validateFields;
                if (!$this->validate->validateData($discountPrice)) {
                    return $this->returnResponse();
                } else {
                    $code = $discountPrice['targetId'];
                    if ($discountPrice['startDate'] != '') {
                        $startDate = date('Y-m-d H:i:s', strtotime($discountPrice['startDate']));
                    } else {
                        $startDate = null;
                    }
                    if ($discountPrice['endDate'] != '') {
                        $endDate = date('Y-m-d H:i:s', strtotime($discountPrice['endDate']));
                    } else {
                        $endDate = null;
                    }

                    $salesType = strtoupper(str_replace(" ", "_", $discountPrice['salesType']));
                    $cdgType = Helper::getCdgType($salesType);
                    $type = strtoupper($discountPrice['type']);
                    $idgType = Helper::getCdgType($type);
                    $discount = $this->discountcalculationModel->create();
                    $discount->setSalesType($cdgType);
                    $discount->setSalesCode($discountPrice['salesCode']);
                    $discount->setType($idgType);
                    $discount->setCode($code);
                    $discount->setQty($discountPrice['qty']);
                    $discount->setPrice($discountPrice['price']);
                    $discount->setStartDt($startDate);
                    $discount->setEndDt($endDate);
                    $discount->setCreatedDt($this->date->gmtDate());
                    $discount->setUpdatedDt($this->date->gmtDate());
                    $discount->Save();
                }
            }

            // phpcs:disable
            $this->dataHelper->unsetGlobalValue('i95_observer_skip');
            $jsondata = json_encode(["entityCode" => $entityCode,
            "targetId" => $stringData['targetId'],
            "source" => "ERP"]);
            $this->dataHelper->coreRegistry->unregister('savingSource');
            $this->dataHelper->coreRegistry->register('savingSource', $jsondata);
            $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_' . $entityCode;
            $this->eventManager->dispatch($aftereventname, ['currentObject' => $this]);
            return $this->abstractDataPersistence->setResponse(
                Data::SUCCESS,
                "Record Successfully Synced",
                1
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
    /**
     * Deletes existing discount calculations
     *
     * @param string $stringData
     */
    public function deleteExistingData($stringData)
    {
        $existingDiscountCollection = $this->discountcalculationModel->create()->getCollection()
            ->addFieldtoFilter('code', $stringData['targetId']);
        if (count($existingDiscountCollection->getData()) > 0) {
            foreach ($existingDiscountCollection as $item) {
                $item->delete();
            }
        }
    }
}
