<?php
/**
 * @namespace   Crimson
 * @module      Shipping
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/24/2019
 */
namespace Crimson\Shipping\Model\ResourceModel\Carrier;

use Crimson\Shipping\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Import;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQuery;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQueryFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Tableratesurepost
 * @package Crimson\Shipping\Model\ResourceModel\Carrier
 */
class Tableratesurepost extends AbstractDb
{
    const VALID_ZIP_CODE_DIGITS = 3;

    /**
     * Import table rates website ID
     *
     * @var int
     */
    protected $_importWebsiteId = 0;

    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors = [];

    /**
     * Count of imported table rates
     *
     * @var int
     */
    protected $_importedRows = 0;

    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $_importUniqueHash = [];

    /**
     * Array of countries keyed by iso2 code
     *
     * @var array
     */
    protected $_importIso2Countries;

    /**
     * Array of countries keyed by iso3 code
     *
     * @var array
     */
    protected $_importIso3Countries;

    /**
     * Associative array of countries and regions
     * [country_id][region_code] = region_id
     *
     * @var array
     */
    protected $_importRegions;

    /**
     * Import Table Rate condition name
     *
     * @var string
     */
    protected $_importConditionName;

    /**
     * Array of condition full names
     *
     * @var array
     */
    protected $_conditionFullNames = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $coreConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Crimson\Shipping\Model\Carrier\Tableratesurepost
     */
    protected $carrierTableratesurepost;

    /**
     * Filesystem instance
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Import
     */
    private $import;

    /**
     * @var RateQueryFactory
     */
    private $rateQueryFactory;

    protected $_helper;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        ScopeConfigInterface $coreConfig,
        StoreManagerInterface $storeManager,
        \Crimson\Shipping\Model\Carrier\Tableratesurepost $carrierTableratesurepost,
        Filesystem $filesystem,
        Import $import,
        RateQueryFactory $rateQueryFactory,
        Data $helper,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->coreConfig = $coreConfig;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->carrierTableratesurepost = $carrierTableratesurepost;
        $this->filesystem = $filesystem;
        $this->import = $import;
        $this->rateQueryFactory = $rateQueryFactory;
        $this->_helper = $helper;
    }

    /**
     * Define main table and id field name
     */
    protected function _construct()
    {
        $this->_init('shipping_tablerate_surepost', 'pk');
    }

    /**
     * @param RateRequest $request
     * @return mixed
     * @throws LocalizedException
     */
    public function getRate(RateRequest $request)
    {
        $validDigits = $this->_helper->getValidZipCodeDigits();
        $postCode = $request->getDestPostcode();
        $modifyResult = '';
        if (!empty($request->getDestPostcode()) &&
            strlen($request->getDestPostcode()) > 0 &&
            $validDigits
        ) {
            $wildCardZipCode = substr($postCode,0,self::VALID_ZIP_CODE_DIGITS);
            $postCode = $wildCardZipCode . '*';
            $modifyResult = $request->getDestPostcode();
        }

        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable());
        /** @var RateQuery $rateQuery */
        $rateQuery = $this->rateQueryFactory->create(['request' => $request]);

        $rateQuery->prepareSelect($select);

        $bindings = [
            ':website_id' => (int) $request->getWebsiteId(),
            ':country_id' => $request->getDestCountryId(),
            ':region_id' => (int) $request->getDestRegionId(),
            ':postcode' => $postCode,
            ':postcode_prefix' => $this->getDestPostcodePrefix($postCode)
        ];

        // Render condition by condition name
        if (is_array($request->getConditionName())) {
            $i = 0;
            foreach ($request->getConditionName() as $conditionName) {
                $bindNameKey = sprintf(':condition_name_%d', $i);
                $bindValueKey = sprintf(':condition_value_%d', $i);
                $bindings[$bindNameKey] = $conditionName;
                $bindings[$bindValueKey] = $request->getData($conditionName);
                $i++;
            }
        } else {
            $bindings[':condition_name'] = $request->getConditionName();
            $bindings[':condition_value'] = round($request->getData($request->getConditionName()) ?: 0.00, 4);
        }

        $result = $connection->fetchRow($select, $bindings);
        // Normalize destination zip code
        if ($result && $result['dest_zip'] == '*') {
            $result['dest_zip'] = '';
        } elseif ($result && !empty($modifyResult)) {
            $result['dest_zip'] = $modifyResult;
        }

        return $result;
    }

    /**
     * @param array $condition
     * @return $this
     * @throws LocalizedException
     */
    private function deleteByCondition(array $condition): Tableratesurepost
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        $connection->delete($this->getMainTable(), $condition);
        $connection->commit();

        return $this;
    }

    /**
     * @param array $fields
     * @param array $values
     * @throws LocalizedException
     */
    private function importData(array $fields, array $values)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            if (count($fields) && count($values)) {
                $this->getConnection()->insertArray($this->getMainTable(), $fields, $values);
                $this->_importedRows += count($values);
            }
        } catch (LocalizedException $e) {
            $connection->rollBack();
            throw new LocalizedException(__('Unable to import data'), $e);
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->critical($e);
            throw new LocalizedException(
                __('Something went wrong while importing table rates Surepost.')
            );
        }
        $connection->commit();
    }

    /**
     * @param DataObject $object
     * @return $this
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function uploadAndImport(DataObject $object)
    {
        /**
         * @var Value $object
         */
        if (empty($_FILES['groups']['tmp_name']['tablerate_surepost']['fields']['import']['value'])) {
            return $this;
        }
        $filePath = $_FILES['groups']['tmp_name']['tablerate_surepost']['fields']['import']['value'];

        $websiteId = $this->storeManager->getWebsite($object->getScopeId())->getId();
        $conditionName = $this->getConditionName($object);

        $file = $this->getCsvFile($filePath);
        try {
            // delete old data by website and condition name
            $condition = [
                'website_id = ?' => $websiteId,
                'condition_name = ?' => $conditionName,
            ];
            $this->deleteByCondition($condition);

            $columns = $this->import->getColumns();
            $conditionFullName = $this->_getConditionFullName($conditionName);
            foreach ($this->import->getData($file, $websiteId, $conditionName, $conditionFullName) as $bunch) {
                $this->importData($columns, $bunch);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(
                __('Something went wrong while importing table rates.')
            );
        } finally {
            $file->close();
        }

        if ($this->import->hasErrors()) {
            $error = __(
                'We couldn\'t import this file because of these errors: %1',
                implode(" \n", $this->import->getErrors())
            );
            throw new LocalizedException($error);
        }
    }

    /**
     * @param DataObject $object
     * @return mixed|string
     */
    public function getConditionName(DataObject $object)
    {
        if ($object->getData('groups/tablerate_surepost/fields/condition_name/inherit') == '1') {
            $conditionName = (string)$this->coreConfig->getValue('carriers/tablerate_surepost/condition_name', 'default');
        } else {
            $conditionName = $object->getData('groups/tablerate_surepost/fields/condition_name/value');
        }
        return $conditionName;
    }

    /**
     * @param $filePath
     * @return Filesystem\File\ReadInterface
     * @throws FileSystemException
     */
    private function getCsvFile($filePath): Filesystem\File\ReadInterface
    {
        $pathInfo = pathinfo($filePath);
        $dirName = isset($pathInfo['dirname']) ? $pathInfo['dirname'] : '';
        $fileName = isset($pathInfo['basename']) ? $pathInfo['basename'] : '';

        $directoryRead = $this->filesystem->getDirectoryReadByPath($dirName);

        return $directoryRead->openFile($fileName);
    }

    /**
     * Return import condition full name by condition name code
     *
     * @param $conditionName
     * @return mixed
     */
    protected function _getConditionFullName($conditionName)
    {
        if (!isset($this->_conditionFullNames[$conditionName])) {
            $name = $this->carrierTableratesurepost->getCode('condition_name_short', $conditionName);
            $this->_conditionFullNames[$conditionName] = $name;
        }

        return $this->_conditionFullNames[$conditionName];
    }

    /**
     * Save import data batch
     *
     * @param array $data
     * @return $this
     * @throws LocalizedException
     */
    protected function _saveImportData(array $data): Tableratesurepost
    {
        if (!empty($data)) {
            $columns = [
                'website_id',
                'dest_country_id',
                'dest_region_id',
                'dest_zip',
                'condition_name',
                'condition_value',
                'price',
            ];
            $this->getConnection()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }

    /**
     * @param $zipCode
     * @return mixed
     */
    private function getDestPostcodePrefix($zipCode)
    {
        if (!preg_match("/^(.+)-(.+)$/", $zipCode ?: '', $zipParts)) {
            return $zipCode;
        }

        return $zipParts[1];
    }
}
