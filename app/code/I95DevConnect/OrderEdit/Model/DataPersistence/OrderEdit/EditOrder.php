<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_OrderEdit
 */
namespace I95DevConnect\OrderEdit\Model\DataPersistence\OrderEdit;

use \I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\OrderEdit\Model\DataPersistence\Validate;
use Magento\Framework\Exception\LocalizedException;
use \Magento\Store\Model\ScopeInterface;
use I95DevConnect\MessageQueue\Helper\Data as MQHelper;
use I95DevConnect\OrderEdit\Helper\Data;

/**
 * Edit order class check Extension is enabled or not
 */
class EditOrder
{
    public const TARGET_ORDER_EDIT_STATUS = 'targetOrderEditStatus';
    /**
     * @var Edit
     */
    public $edit;

    /**
     * @var Update
     */
    public $update;

    /**
     *
     * @var Validate
     */
    public $editOrderValidator;
    /**
     * @var string[]
     */
    public $requiredFields = [
        'targetId'=>'edit_order_002',
        self::TARGET_ORDER_EDIT_STATUS =>'edit_order_003'
    ];

    /**
     * @var MQHelper
     */
    public $mqHelper;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @param Edit $edit
     * @param Update $update
     * @param Validate $editOrderValidator
     * @param MQHelper $mqHelper
     * @param Data $dataHelper
     */
    public function __construct(
        Edit $edit,
        Update $update,
        Validate $editOrderValidator,
        MQHelper $mqHelper,
        Data $dataHelper
    ) {
        $this->edit = $edit;
        $this->update = $update;
        $this->editOrderValidator = $editOrderValidator;
        $this->mqHelper = $mqHelper;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Check order edit status and update the order
     *
     * @param array $stringData
     * @return string
     * @throws LocalizedException
     */
    public function edit($stringData)
    {
        try {
            $component = $this->mqHelper->getComponent();
            if ($component === 'D365FO') {
                unset($this->requiredFields[self::TARGET_ORDER_EDIT_STATUS]);
                $stringData[self::TARGET_ORDER_EDIT_STATUS] = $this->dataHelper->getOrderStatus($stringData);
            }
            $this->editOrderValidator->validateData($stringData, $this->requiredFields);
            if ($stringData[self::TARGET_ORDER_EDIT_STATUS] === 'edited') {
                return $this->edit->editOrder($stringData);
            } elseif ($stringData[self::TARGET_ORDER_EDIT_STATUS] === 'updated') {
                return $this->update->updateOrder($stringData);
            } else {
                throw new LocalizedException(__('edit_order_007'));
            }
        } catch (\Exception $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }
}
