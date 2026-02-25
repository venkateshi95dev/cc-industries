<?php

namespace Crimson\CorvetteCentralImport\Service;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem;

class ImportCCProducts
{
    CONST SUCCESS_CODE         = "success";
    CONST CSV_FILES_PATH       = "/var/import/ImportCCProducts/ProductData/";
    CONST CSV_1ST_MAIN_IMPORT_FILE = "Crimson Ecommerce.csv";
    CONST CSV_2ND_MAIN_IMPORT_FILE = "Item File - SKU_Dims_Status.csv";
    CONST CSV_3rd_MAIN_IMPORT_FILE = "exported_product_pages_v.4.0-pb_2025-07-14.csv";
    CONST CSV_4th_MAIN_IMPORT_FILE = "Ecomm Parent Child Crimson Data - Product Families and Variant Attributes(SKUs with Variation Attributes).csv";
    CONST CSV_5th_MAIN_IMPORT_FILE = "COKG Products by Att Set v2 2025-06-26.csv";
    CONST CSV_MAIN_FILE            = "crimson_products_data_for_import.csv";
    CONST BATCH_SIZE               = 1000;

    protected array $_headerCols = [];

    public function __construct(
        protected Csv $csvProcessor,
        protected DirectoryList $directoryList,
        protected File $file,
        protected Filesystem $filesystem
    ) {}

    /**
     * @return string[]
     */
    public function execute(): array
    {
        $result = [
            'result'   => self::SUCCESS_CODE,
            'message' => ''
        ];

        try {
            // get root path
            $rootPath = $this->directoryList->getRoot();
            // get files path
            $filesPath = $rootPath . self::CSV_FILES_PATH;

            //processing 1st main file
            $mainFileDataExport = [];
            $mainFileDataExport = $this->_processFirstMainFile($mainFileDataExport);
            if (!$mainFileDataExport) {
                throw new LocalizedException(__('No data after processing 1st main file.'));
            }

            //processing 2nd main file
            $mainFileDataExport = $this->_processSecondMainFile($mainFileDataExport);

            //processing 3rd main file
            $mainFileDataExport = $this->_processThirdMainFile($mainFileDataExport);

            //processing 4th main file
            $mainFileDataExport = $this->_processFourthMainFile($mainFileDataExport);

            //processing 5th main file(Attr set)
            //$mainFileDataExport = $this->_processFifthMainFile($mainFileDataExport);

            //creating main export CSV file
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $streamMainFileExport = $directory->openFile($filesPath . self::CSV_MAIN_FILE, 'w+');
            $streamMainFileExport->lock();
            foreach (array_chunk($mainFileDataExport, self::BATCH_SIZE, true) as $currentRows) {
                foreach ($currentRows as $sku => $row) {
                    if (empty($this->_headerCols)) {
                        $this->_setHeaderCols($streamMainFileExport, array_keys($row));
                    }

                    if (empty($row['url_key'])) {
                        $row['url_key'] = $this->_getPathUrl((string)$row['name'], (string)$row['sku']);
                    }

                    $streamMainFileExport->writeCsv(array_merge($this->_headerCols, array_intersect_key($row, $this->_headerCols)));
                }
            }

            //finishing with the file
            $streamMainFileExport->unlock();
            $streamMainFileExport->close();

            // logging messages
            $result['message'] = __('All files have been processed.');

        } catch (LocalizedException | \Exception $e) {
            $result['status']   = 'error';
            $result['message'] = __('We can\'t import the files right now: %1', $e->getMessage());
        } finally {
            return $result;
        }
    }

    private function _processFifthMainFile(array $mainFileDataExport): array
    {
        // get root path
        $rootPath = $this->directoryList->getRoot();
        // get files path
        $filesPath = $rootPath . self::CSV_FILES_PATH;

        // getting main file
        $mainFileDataImport = $this->csvProcessor->getData($filesPath . ImportCCProducts::CSV_5th_MAIN_IMPORT_FILE);
        array_shift($mainFileDataImport);
        if (!$mainFileDataImport) {
            throw new LocalizedException(__('No data in 5th main file.'));
        }

        foreach (array_chunk($mainFileDataImport, self::BATCH_SIZE, true) as $currentRows) {
            foreach ($currentRows as $row) {
                if (empty($row[0]) || !isset($mainFileDataExport[$row[0]])) {
                    continue;
                }

                $mainFileDataExport[$row[0]]['attribute_set_code'] = !empty($row[1]) ? trim($row[1]) : '';
            }
        }

        return $mainFileDataExport;
    }

    private function _processFourthMainFile(array $mainFileDataExport): array
    {
        // get root path
        $rootPath = $this->directoryList->getRoot();
        // get files path
        $filesPath = $rootPath . self::CSV_FILES_PATH;

        // getting main file
        $mainFileDataImport = $this->csvProcessor->getData($filesPath . ImportCCProducts::CSV_4th_MAIN_IMPORT_FILE);
        array_shift($mainFileDataImport);
        if (!$mainFileDataImport) {
            throw new LocalizedException(__('No data in 4th main file.'));
        }

        foreach (array_chunk($mainFileDataImport, self::BATCH_SIZE, true) as $currentRows) {
            foreach ($currentRows as $row) {
                $parentSku = trim($row[1] ?? '');
                $childSku  = trim($row[0] ?? '');

                if (empty($childSku) ||
                    !isset($mainFileDataExport[$childSku]) ||
                    empty($parentSku) ||
                    !isset($mainFileDataExport[$parentSku])
                ) {
                    continue;
                }

                $mainFileDataExport[$childSku]['cc_color']        = trim($row[4] ?? '');
                $mainFileDataExport[$childSku]['cc_body_style']   = !empty($row[3]) ? trim($row[3]) : trim($row[5] ?? '');
                $mainFileDataExport[$childSku]['cc_code']         = trim($row[6] ?? '');
                $mainFileDataExport[$childSku]['cc_logo']         = trim($row[7] ?? '');
                $mainFileDataExport[$childSku]['cc_date']         = trim($row[8] ?? '');
                $mainFileDataExport[$childSku]['cc_model']        = trim($row[9] ?? '');
                $mainFileDataExport[$childSku]['cc_option']       = trim($row[10] ?? '');
                $mainFileDataExport[$childSku]['cc_quarter']      = trim($row[11] ?? '');
                $mainFileDataExport[$childSku]['cc_ratio']        = trim($row[12] ?? '');
                $mainFileDataExport[$childSku]['cc_size']         = trim($row[13] ?? '');
                $mainFileDataExport[$childSku]['cc_style']        = trim($row[14] ?? '');
                $mainFileDataExport[$childSku]['cc_year']         = trim($row[15] ?? '');
                $mainFileDataExport[$childSku]['cc_exterior']     = trim($row[16] ?? '');
                $mainFileDataExport[$childSku]['cc_transmission'] = trim($row[17] ?? '');
                $mainFileDataExport[$childSku]['cc_trim']         = trim($row[18] ?? '');
                $mainFileDataExport[$childSku]['cc_interior']     = trim($row[19] ?? '');
                $mainFileDataExport[$childSku]['cc_cove']         = trim($row[20] ?? '');
                $mainFileDataExport[$childSku]['cc_stinger']      = trim($row[21] ?? '');
                $mainFileDataExport[$childSku]['visibility']      = 'Not Visible Individually';

                $variationInfo = '';
                if (!empty($mainFileDataExport[$childSku]['cc_color'])) {
                    $variationInfo .= ",cc_color=" . $mainFileDataExport[$childSku]['cc_color'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_body_style'])) {
                    $variationInfo .= ",cc_body_style=" . $mainFileDataExport[$childSku]['cc_body_style'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_code'])) {
                    $variationInfo .= ",cc_code=" . $mainFileDataExport[$childSku]['cc_code'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_logo'])) {
                    $variationInfo .= ",cc_logo=" . $mainFileDataExport[$childSku]['cc_logo'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_date'])) {
                    $variationInfo .= ",cc_date=" . $mainFileDataExport[$childSku]['cc_date'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_model'])) {
                    $variationInfo .= ",cc_model=" . $mainFileDataExport[$childSku]['cc_model'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_option'])) {
                    $variationInfo .= ",cc_option=" . $mainFileDataExport[$childSku]['cc_option'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_quarter'])) {
                    $variationInfo .= ",cc_quarter=" . $mainFileDataExport[$childSku]['cc_quarter'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_ratio'])) {
                    $variationInfo .= ",cc_ratio=" . $mainFileDataExport[$childSku]['cc_ratio'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_size'])) {
                    $variationInfo .= ",cc_size=" . $mainFileDataExport[$childSku]['cc_size'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_style'])) {
                    $variationInfo .= ",cc_style=" . $mainFileDataExport[$childSku]['cc_style'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_year'])) {
                    $variationInfo .= ",cc_year=" . $mainFileDataExport[$childSku]['cc_year'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_exterior'])) {
                    $variationInfo .= ",cc_exterior=" . $mainFileDataExport[$childSku]['cc_exterior'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_transmission'])) {
                    $variationInfo .= ",cc_transmission=" . $mainFileDataExport[$childSku]['cc_transmission'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_trim'])) {
                    $variationInfo .= ",cc_trim=" . $mainFileDataExport[$childSku]['cc_trim'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_interior'])) {
                    $variationInfo .= ",cc_interior=" . $mainFileDataExport[$childSku]['cc_interior'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_cove'])) {
                    $variationInfo .= ",cc_cove=" . $mainFileDataExport[$childSku]['cc_cove'];
                }

                if (!empty($mainFileDataExport[$childSku]['cc_stinger'])) {
                    $variationInfo .= ",cc_stinger=" . $mainFileDataExport[$childSku]['cc_stinger'];
                }

                if (!$variationInfo) {
                    continue;
                }

                $variationInfo = 'sku=' . $childSku . $variationInfo;
                if (!empty($mainFileDataExport[$parentSku]['configurable_variations'])) {
                    $variationInfo = $mainFileDataExport[$parentSku]['configurable_variations'] . "|" . $variationInfo;
                }

                $mainFileDataExport[$parentSku]['configurable_variations'] = $variationInfo;
                $mainFileDataExport[$parentSku]['product_type']            = 'configurable';
            }
        }

        return $mainFileDataExport;
    }

    private function _processThirdMainFile(array $mainFileDataExport): array
    {
        // get root path
        $rootPath = $this->directoryList->getRoot();
        // get files path
        $filesPath = $rootPath . self::CSV_FILES_PATH;

        // getting main file
        $mainFileDataImport = $this->csvProcessor->getData($filesPath . ImportCCProducts::CSV_3rd_MAIN_IMPORT_FILE);
        array_shift($mainFileDataImport);
        if (!$mainFileDataImport) {
            throw new LocalizedException(__('No data in 3rd main file.'));
        }

        foreach (array_chunk($mainFileDataImport, self::BATCH_SIZE, true) as $currentRows) {
            foreach ($currentRows as $row) {
                if (empty($row[0]) || !isset($mainFileDataExport[$row[0]])) {
                    continue;
                }

                $mainFileDataExport[$row[0]]['url_key'] = !empty($row[1]) ? str_replace("https://www.corvettecentral.com/", "", trim($row[1])) : '';
            }
        }

        return $mainFileDataExport;
    }

    private function _processSecondMainFile(array $mainFileDataExport): array
    {
        // get root path
        $rootPath = $this->directoryList->getRoot();
        // get files path
        $filesPath = $rootPath . self::CSV_FILES_PATH;

        // getting main file
        $mainFileDataImport = $this->csvProcessor->getData($filesPath . ImportCCProducts::CSV_2ND_MAIN_IMPORT_FILE);
        array_shift($mainFileDataImport);
        if (!$mainFileDataImport) {
            throw new LocalizedException(__('No data in 2nd main file.'));
        }

        foreach (array_chunk($mainFileDataImport, self::BATCH_SIZE, true) as $currentRows) {
            foreach ($currentRows as $row) {
                if (empty($row[0]) || !isset($mainFileDataExport[$row[0]])) {
                    continue;
                }

                $mainFileDataExport[$row[0]]['cc_base_unit_measure'] = trim($row[2] ?? '');
                $mainFileDataExport[$row[0]]['price']  = number_format((float)$row[9], 2, '.', '');
                $mainFileDataExport[$row[0]]['weight'] = number_format((float)$row[11], 4, '.', '');
                $mainFileDataExport[$row[0]]['cc_truck_freight_type'] = trim($row[19] ?? '');
                $mainFileDataExport[$row[0]]['cc_dropship_only'] = strtolower(trim($row[32] ?? '')) === "true" ? "Yes" : "No";
                $mainFileDataExport[$row[0]]['cc_may_ship_from_manufacturer'] = strtolower(trim($row[33] ?? '')) === "true" ? "Yes" : "No";
                $mainFileDataExport[$row[0]]['cc_core_charge']          = trim($row[39] ?? 0);
                $mainFileDataExport[$row[0]]['cc_core_charge_required'] = strtolower(trim($row[40] ?? '')) === "true" ? "Yes" : "No";
                $mainFileDataExport[$row[0]]['cc_packaging_charge']     = trim($row[49] ?? 0);
                $mainFileDataExport[$row[0]]['cc_freight_charge']       = trim($row[50] ?? 0);
                $mainFileDataExport[$row[0]]['cc_ecommerce_logo']       = trim($row[55] ?? '');
            }
        }

        return $mainFileDataExport;
    }

    private function _processFirstMainFile(array $mainFileDataExport): array
    {
        // get root path
        $rootPath = $this->directoryList->getRoot();
        // get files path
        $filesPath = $rootPath . self::CSV_FILES_PATH;

        // getting main file
        $mainFileDataImport = $this->csvProcessor->getData($filesPath . ImportCCProducts::CSV_1ST_MAIN_IMPORT_FILE);
        array_shift($mainFileDataImport);
        if (!$mainFileDataImport) {
            throw new LocalizedException(__('No data in 1st main file.'));
        }

        foreach (array_chunk($mainFileDataImport, self::BATCH_SIZE, true) as $currentRows) {
            foreach ($currentRows as $row) {
                if (empty($row[0]) || empty($row[11])) {
                    continue;
                }

                $mainFileDataExport[$row[0]] = [
                    "sku"                => trim($row[0]),
                    "product_websites"   => CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE,
                    "attribute_set_code" => 'Default',
                    "name"             => trim(str_replace("?", "", mb_convert_encoding($row[11], 'UTF-8', 'UTF-8'))),
                    "product_type"     => !empty($row[18]) ? trim($row[18]) : 'simple',
                    "price"            => '0.00',
                    "description"      => mb_convert_encoding($this->_adjustDescription($row), 'UTF-8', 'UTF-8'),
                    "meta_description" => trim(mb_convert_encoding($row[16] ?? '', 'UTF-8', 'UTF-8')),
                    "meta_title"       => trim(mb_convert_encoding($row[17] ?? '', 'UTF-8', 'UTF-8')),
                    "url_key"          => "",
                    "product_online"   => 1,
                    "configurable_variations"   => '',
                    "cc_color"                  => '',
                    "cc_body_style"             => '',
                    "cc_code"                   => '',
                    "cc_logo"                   => '',
                    "cc_date"                   => '',
                    "cc_model"         => '',
                    "cc_option"        => '',
                    "cc_quarter"       => '',
                    "cc_ratio"         => '',
                    "cc_size"          => '',
                    "cc_style"         => '',
                    "cc_year"          => '',
                    "cc_exterior"      => '',
                    "cc_transmission"  => '',
                    "cc_trim"          => '',
                    "cc_interior"      => '',
                    "cc_cove"          => '',
                    "cc_stinger"       => '',
                    "cc_truck_freight_type"         => '',
                    "cc_core_charge"                => '',
                    "cc_core_charge_required"       => '',
                    "cc_packaging_charge"           => '',
                    "cc_freight_charge"             => '',
                    "cc_ecommerce_logo"             => '',
                    "cc_may_ship_from_manufacturer" => '',
                    "cc_dropship_only"              => '',
                    "visibility"                    => 'Catalog, Search',
                ];
            }
        }

        return $mainFileDataExport;
    }

    protected function _adjustDescription($rowData): string
    {
        return ($rowData[1] ?? '') .
            ($rowData[2] ?? '') .
            ($rowData[3] ?? '') .
            ($rowData[4] ?? '') .
            ($rowData[5] ?? '') .
            ($rowData[6] ?? '') .
            ($rowData[7] ?? '') .
            ($rowData[8] ?? '') .
            ($rowData[9] ?? '') .
            ($rowData[10] ?? '');
    }

    protected function _setHeaderCols($stream, array $headerColumns): void
    {
        if (!empty($this->_headerCols)) {
            throw new LocalizedException(__('The header column names are already set.'));
        }

        if ($headerColumns) {
            foreach ($headerColumns as $columnName) {
                $this->_headerCols[$columnName] = false;
            }

            $stream->writeCsv(array_keys($this->_headerCols));
        }
    }

    private function _getPathUrl(string $name, string $sku): string
    {
        $search = [' ', '/', '"', '\'', '*'];
        $replace = ['-', '-', '', '', ''];

        return str_replace($search, $replace, strtolower($name)) . "-" . $sku;
    }
}
