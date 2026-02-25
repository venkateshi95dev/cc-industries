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

namespace Ebizcharge\Ebizcharge\Cron;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ProductFactory;

/**
 * Check if An item is available to stock
 *
 * Class CheckItemStock
 */
class CheckItemStock
{


    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;

    /**
     * CheckItemStock constructor.
     *
     * @param ProductFactory $productFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        ProductFactory $productFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {

        /** @var _productFactory */
        $this->_productFactory = $productFactory;
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Execute Method
     *
     * @return void
     */
    public function execute()
    {
        $this->_ebizchargeLogger->addInfo(__(
            'Check Item Stock Cron has started at: ' . date("Y-m-d h:i:sa")
        ));

        try {
            /** low stock email run during stock */
            $this->_productFactory->create()->prepareStockItemsForLowStockEmail();

        } catch (\Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                'Exception occurred during Checking stock item Error:'.$exception->getMessage()
            ));
        }
    }
}
