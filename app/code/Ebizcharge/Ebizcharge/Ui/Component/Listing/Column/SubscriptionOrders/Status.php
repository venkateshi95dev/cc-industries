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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\SubscriptionOrders;

use Ebizcharge\Ebizcharge\Model\Config;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * This class is used to add different classes to status as per status value
 *
 * Class Status
 */
class Status extends Column
{
    /**
     * recurring order status
     *
     * @const STATUS_ON
     */
    public const STATUS_ON = "on";

    /**
     * Status Off
     *
     * @const STATUS_OFF
     */
    public const STATUS_OFF = "off";

    /**
     * STATUS_DELETED
     *
     * @const STATUS_DELETED
     */
    public const STATUS_DELETED = "del";

    /**
     * @var Config
     */
    protected Config $_ebizchargeConfigModel;

    /**
     * @var Http
     */
    protected Http $_httpRequest;

    /**
     * ResultStatus constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Config $ebizchargeConfigModel
     * @param Http $httpRequest
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Config $ebizchargeConfigModel,
        Http $httpRequest,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        /** @var _ebizchargeConfigModel */
        $this->_ebizchargeConfigModel = $ebizchargeConfigModel;
        /** @var _httpRequest */
        $this->_httpRequest = $httpRequest;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $status = $item[$this->getData('name')];

                    $item[$this->getData('name')] =  $status;

                }
            }
        }

        return $dataSource;
    }
}
