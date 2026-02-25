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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Recurring grid actions column
 *
 * Class RecurringActions
 */
class RecurringActions extends Column
{
    /**
     * URL Path
     *
     * @const URL_PATH_EDIT
     */
    public const URL_PATH_EDIT = 'ebizcharge_ebizcharge/recurrings/editaction';

    /**
     * @const URL_PATH_EXPORT
     */
    public const URL_PATH_EXPORT = 'ebizcharge_ebizcharge/recurrings/datesexportaction';

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * Main class Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface       $urlBuilder,
        array              $components = [],
        array              $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var  urlBuilder */
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                if (isset($item['entity_id'])) {
                    $customerId = $item['customer_id'] ?? "";
                    $subScribedId = $item['entity_id'] ?? "";
                    $scheduledId = $item['eb_rec_scheduled_payment_internal_id'] ?? "";
                    /** @var  $editUrl */
                    $editUrl = $this->urlBuilder->getUrl(self::URL_PATH_EDIT, [
                        "magcid" => $customerId,
                        "rid" => $subScribedId,
                        "mid" => $scheduledId

                    ]);
                    /** @var  $exportUrl */
                    $exportUrl = $this->urlBuilder->getUrl(self::URL_PATH_EXPORT, [
                        "mid" => $scheduledId,
                        "rid" => $subScribedId
                    ]);

                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $editUrl,
                            'label' => __('View | Edit')
                        ],
                        'export' => [
                            'href' => $exportUrl,
                            'label' => __('Export')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
