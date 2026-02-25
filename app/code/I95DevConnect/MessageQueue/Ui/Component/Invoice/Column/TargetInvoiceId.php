<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Ui\Component\Invoice\Column;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\SalesInvoiceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class add  TargetInvoiceId
 */
class TargetInvoiceId extends Column
{
    /**
     * @var SalesInvoiceFactory
     */
    public $customSalesInvoice;

    /**
     * @var Data
     */
    public $helperData;

    /**
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SalesInvoiceFactory $customSalesInvoice
     * @param Data $helperData
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SalesInvoiceFactory $customSalesInvoice,
        Data $helperData,
        array $components = [],
        array $data = []
    ) {

        $this->customSalesInvoice = $customSalesInvoice;
        $this->helperData = $helperData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare
     *
     * @throws LocalizedException
     */
    public function prepare()
    {
        if (!$this->helperData->isEnabledInAnyWebsite()) {
            $this->setData(
                'config',
                array_replace_recursive(
                    ['componentDisabled' => true],
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
                $customInvoiceModel = $this->customSalesInvoice->create();
                $customInvoiceData = $customInvoiceModel
                        ->getCollection()
                        ->addFieldToSelect('target_invoice_id')
                        ->addFieldToFilter('source_invoice_id', $item['increment_id']);
                if ($customInvoiceData->getSize() > 0) {
                    foreach ($customInvoiceData as $customInvoiceDataCollection) {
                        $target_invoice_id = $customInvoiceDataCollection->getTargetInvoiceId();
                        $item['target_invoice_id'] = strlen($target_invoice_id) > 1 ?
                            $target_invoice_id : '';
                    }
                }
            }
        }
        return $dataSource;
    }
}
