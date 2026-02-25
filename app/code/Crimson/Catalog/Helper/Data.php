<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        4/1/2019 9:33 AM
 * @brief
 */
namespace Crimson\Catalog\Helper;

use Crimson\Catalog\Service\ServiceProduct;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Data
 * @package Crimson\Catalog\Helper
 */
class Data extends AbstractHelper
{

    const SATURDAY_INT_DAY = 6;
    const FRIDAY_INT_DAY   = 5;

    public function __construct(
        Context             $context,
        protected StockItemRepository $_stockItemRepository,
        protected ServiceProduct $serviceProduct,
        protected TimezoneInterface   $_timezone
    )
    {
        parent::__construct($context);
    }

    public function getStockItem($productId)
    {
        return $this->_stockItemRepository->get($productId);
    }

    public function getCurrentTime(): string
    {
        return $this->_timezone->date()->format('H:i:s');;
    }

    /**
     * @return \DateTime
     */
    public function getCurrentDateTime(): \DateTime
    {
        return $this->_timezone->date();
    }

    /**
     * @return string
     */
    public function getShippingTimeInfo(): string
    {
        $dateTime = $this->getCurrentDateTime();
        $dayOfWeekInt = $dateTime->format('N');
        $time = $dateTime->format('H:i:s');

        //checking Weekend days or Friday passed 3pm ET
        if ($dayOfWeekInt >= self::SATURDAY_INT_DAY ||
            ($dayOfWeekInt == self::FRIDAY_INT_DAY && $time >= '14:55:00')
        ) {
            $shippingTime = __('Ships Monday');
        } elseif ($time >= '14:55:00') {
            $shippingTime = __('Ships Tomorrow');
        } else {
            $shippingTime = __('Ships today if ordered by 3:00 PM ET');
        }

        return $shippingTime;
    }

    public function isServiceProduct($product): bool
    {
        return $this->serviceProduct->is($product);
    }
}
