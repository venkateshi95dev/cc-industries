<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\SyncAssets\Logs;

use Ebizcharge\Ebizcharge\Console\EbizchargeCommandsCli;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\File\Csv;

/**
 * Sync Assets Logs View
 *
 * Class View
 */
class View extends Template
{
    /**
     * Template for view sync logs
     *
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::syncAssets/logs/view.phtml';

    /**
     * @var Csv
     */
    private $csvProcesser;

    /**
     * @param Csv $csvProcessor
     * @param Context $context
     */
    public function __construct(
        Csv $csvProcessor,
        Context $context
    ) {
        parent::__construct($context);
        $this->csvProcesser = $csvProcessor;
    }

    /**
     * Get sync cron process logs
     *
     * @return array
     */
    public function getLogs()
    {
        try {
            return $this->csvProcesser->getData(EbizchargeCommandsCli::DOWNLOAD_CUSTOMER_ERROR_LOG_FILE);
        } catch (\Exception $e) {
            return [];
        }
    }
}
