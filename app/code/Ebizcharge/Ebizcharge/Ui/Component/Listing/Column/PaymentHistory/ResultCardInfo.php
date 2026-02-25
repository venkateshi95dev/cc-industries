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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\PaymentHistory;

use Ebizcharge\Ebizcharge\Model\Config;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * This class is used to add different classes to status as per status value
 *
 * Class ResultCardInfo
 */
class ResultCardInfo extends Column
{
    /**
     * @var Config
     */
    protected Config $_configModel;

    /**
     * ResultCardInfo constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Config $configModel
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Config $configModel,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var _configModel */
        $this->_configModel = $configModel;
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
            $resultCardInfo = $this->getData('name');
            $counter = 0;

            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$resultCardInfo])) {
                    $item[$resultCardInfo] = $this->getCardInfoHtml($item[$resultCardInfo]);
                    $counter++;
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get status html as per $resultCardInfo
     *
     * @param mixed $resultCardInfo
     * @return string
     */
    private function getCardInfoHtml($resultCardInfo = ''): string
    {
        /** @var  $cardsCssIcons */
        $cardsCssIcons = $this->_configModel->getCssCardsIcons();

        $cssClass = 'credit-card-icon';

        if (count($cardsCssIcons) > 0) {
            foreach ($cardsCssIcons as $key => $cardsCssIcon) {
                $resultCardInfoResult = $resultCardInfo;
                $classIndex = strpos(strtolower($resultCardInfoResult), strtolower($key));
                if ($classIndex !== false) {
                    $cssClass = $cardsCssIcon;
                    break;
                }
            }
        }

        $label = $resultCardInfo;

        return '<span class="' . $cssClass . '"><span>' . $label . '</span></span>';
    }
}
