<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_OrderEdit
 */

namespace I95DevConnect\OrderEdit\Model\DataPersistence;

use \I95DevConnect\OrderEdit\Helper\Data;
use \I95DevConnect\MessageQueue\Model\DataPersistence\Validate as MQValidate;
use Magento\Framework\Exception\LocalizedException;

/**
 * I95Dev validator class
 */
class Validate
{
    /**
     *
     * @var MQValidate
     */
    public $validate;

    /**
     *
     * @var Data
     */
    public $editOrderHelper;
    /**
     * @var array
     */
    public $dataString;

    /**
     *
     * @param MQValidate $validate
     * @param Data $editOrderHelper
     */
    public function __construct(
        MQValidate $validate,
        Data $editOrderHelper
    ) {
        $this->validate = $validate;
        $this->editOrderHelper = $editOrderHelper;
    }

    /**
     * Validate the required fields
     *
     * @param string $stringData
     * @param array $requiredFields
     * @throws LocalizedException
     */
    public function validateData($stringData, $requiredFields)
    {
        if (!$this->editOrderHelper->isEnabled()) {
            throw new LocalizedException(__('edit_order_001'));
        }
        $this->validate->validateFields = $requiredFields;
        $this->validate->validateData($stringData);
    }

    /**
     * Validate if the order is eligible for editing.
     *
     * @param string $dataString
     * @return object
     * @throws LocalizedException
     */
    public function validateOrderToEdit($dataString)
    {
        $this->dataString = $dataString;
        $oldOrder = $this->editOrderHelper->getOrder($dataString);
        if ($oldOrder->getId() && $oldOrder->getStatus() != "canceled") {
            if ($oldOrder->hasInvoices()) {
                throw new LocalizedException(__("edit_order_005"));
            }
            if ($oldOrder->hasShipments()) {
                throw new LocalizedException(__("edit_order_006"));
            }
        } else {
            throw new LocalizedException(__('edit_order_004'));
        }
        return $oldOrder;
    }

    /**
     * Validate I95Dev message que sales order info
     *
     * @param array $dataString
     * @return string
     * @throws LocalizedException
     */
    public function validateOrderToUpdate($dataString)
    {
        $this->dataString = $dataString;
        $oldOrder = $this->editOrderHelper->getOrder($dataString);
        if (!$oldOrder->getId() || $oldOrder->getStatus() == "canceled") {
            throw new LocalizedException(__('edit_order_006'));
        }
        return $oldOrder;
    }

    /**
     * Validate address field and data
     *
     * @param array $address
     * @return boolean
     */
    public function validateAddress($address)
    {
        $validateFields = [
            'firstname' => 'i95dev_addr_002',
            'lastname' => 'i95dev_addr_003',
            'country_id' => 'i95dev_addr_004',
            'region_id' => 'i95dev_addr_005',
            'city' => 'i95dev_addr_006',
            'street' => 'i95dev_addr_007',
            'postcode' => 'i95dev_addr_008',
            'telephone' => 'i95dev_addr_009'
        ];
        $this->validate->validateFields = $validateFields;
        $this->validate->validateData($address);
        return true;
    }
}
