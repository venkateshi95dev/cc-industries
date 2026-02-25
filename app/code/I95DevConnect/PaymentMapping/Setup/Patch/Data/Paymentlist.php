<?php
/**
 * @author    Subhan
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\PaymentMapping\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Reverse\Payment;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Paymentlist implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var DateTime
     */
    public $date;
    /**
     * @var FileResolverInterface
     */
    public $fileResolver;
    /**
     * @var Payment
     */
    public $payment;
    /**
     * @var Data
     */
    public $dataHelper;
    /**
     * @var LoggerInterface
     */
    public $logger;
    /**
     * @var State
     */
    public $appState;

    /**
     * Paymentlist constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param FileResolverInterface $fileResolver
     * @param Payment $payment
     * @param Data $dataHelper
     * @param State $appState
     * @param LoggerInterface $logger
     * @param DateTime $date
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        FileResolverInterface $fileResolver,
        Payment $payment,
        Data $dataHelper,
        State $appState,
        LoggerInterface $logger,
        DateTime $date
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->fileResolver = $fileResolver;
        $this->payment = $payment;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->appState = $appState;
        $this->date = $date;

        /* Set the area code and catch exception if thrown */
        try {
            $this->appState->setAreaCode('global');
        } catch (LocalizedException $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $csvRow = [];
        $setup = $this->moduleDataSetup;
        $csvData = $this->fileResolver->get("payment.csv", 'global');
        if (count($csvData) > 0) {
            $activeMethods = $this->payment->getActivePaymentMethods();
            if (!empty($activeMethods)) {
                foreach ($csvData as $content) {
                    $csvRow = str_getcsv($content, "\n");
                }

                $mappingList = $this->prepareListData($csvRow, $activeMethods);

                if (!empty($mappingList)) {
                    $setup->getConnection()->insert(
                        $setup->getTable('i95dev_payment_mapping_list'),
                        [
                            'mapped_data' => json_encode($mappingList),
                            'created_at' => $this->date->gmtDate(),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Preparing data
     *
     * @param string $csvRow
     * @param array $activeMethods
     * @return array
     */
    public function prepareListData($csvRow, $activeMethods)
    {
        $mappingList = [];
        foreach ($csvRow as $mappingData) {
            $currentEntity = explode(",", $mappingData);
            if (in_array($currentEntity[1], $activeMethods)) {
                $mappingList[] = [
                    "ecommerceMethod" => $currentEntity[1],
                    "erpMethod" => $currentEntity[2],
                    "isEcommerceDefault" => $currentEntity[3],
                    "isErpDefault" => $currentEntity[4],
                ];
            }
        }

        return $mappingList;
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get Dependencies
     *
     * @return array
     */
    public static function getDependencies() // NOSONAR
    {
        return [

        ];
    }

    /**
     * Get patch version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.2';
    }
}
