<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ImportExportCore
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ImportExportCore\Plugin;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result;
use Magento\ImportExport\Model\Import;

class ImportMoreButton extends Generic
{
    /**
     * Around Get Import Button Html
     *
     * @param Result $subject
     * @param callable $proceed
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundgetImportButtonHtml(Result $subject, callable $proceed)
    {
        return '&nbsp;&nbsp;<button onclick="varienImport.startImport(\'' .
            $subject->getImportStartUrl() .
            '\', \'' .
            Import::FIELD_NAME_SOURCE_FILE .
            '\');" class="scalable save"' .
            ' type="button"><span><span><span>' .
            __(
                'Import'
            ) . '</span></span></span></button>&nbsp;&nbsp;<button onclick="varienImport.startImport(\'' .
            $subject->getImportStartUrl() .
            '\', \'' .
            Import::FIELD_NAME_SOURCE_FILE .
            '\'); window.open(\'' . $this->getUrl('bssimportexport/importprocess/index') . '\',\'\');"
             class="scalable save"' .
            ' type="button"><span><span><span>' .
            __(
                'Import By Cron'
            ) . '</span></span></span></button>';
    }
}
