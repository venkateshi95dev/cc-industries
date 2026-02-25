<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_Returns
 */

namespace I95DevConnect\Returns\Ui\Component\CreditMemo\Column;

use I95DevConnect\Returns\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Data as MQHelper;
use I95DevConnect\Returns\Model\ReturnsCreditMemoIdsFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class add  TargetInvoiceId
 */
class CreditMemoId extends Column
{

    /**
     * @var ReturnsCreditMemoIdsFactory
     */
    public $customCreditMemo;

    /**
     * @var MQHelper
     */
    public $mqHelperData;

    /**
     * @var Data
     */
    public $helperData;

    /**
     * CreditMemoId constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ReturnsCreditMemoIdsFactory $customCreditMemo
     * @param MQHelper $mqHelperData
     * @param Data $helperData
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ReturnsCreditMemoIdsFactory $customCreditMemo,
        MQHelper $mqHelperData,
        Data $helperData,
        array $components = [],
        array $data = []
    ) {

        $this->customCreditMemo = $customCreditMemo;
        $this->helperData = $helperData;
        $this->mqHelperData = $mqHelperData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare
     *
     * @throws LocalizedException
     */
    public function prepare()
    {
        if (!$this->helperData->isEnabled() && !$this->mqHelperData->isEnabled()) {

            $this->setData(
                'config',
                array_replace_recursive(
                    ['componentDisabled' =>true],
                    (array)$this->getData('config')
                )
            );
        }

        parent::prepare();
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $customCreditMemoModel = $this->customCreditMemo->create();
                $customCreditMemoData = $customCreditMemoModel
                        ->getCollection()
                        ->addFieldToSelect('creditmemo_id')
                        ->addFieldToFilter('magento_creditmemo_id', $item['increment_id']);
                if ($customCreditMemoData->getSize() > 0) {
                    foreach ($customCreditMemoData as $customCreditMemoDataCollection) {
                        $creditmemo_id = $customCreditMemoDataCollection->getCreditmemoId();
                        $item['creditmemo_id'] = strlen($creditmemo_id) > 1 ?
                            $creditmemo_id : '';
                    }
                }
            }
        }
        return $dataSource;
    }
}
