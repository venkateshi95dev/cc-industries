<?php

namespace Crimson\MachShipping\Service;

use Crimson\MachShipping\Model\Carrier\Mach;
use Crimson\MachShipping\Model\Config;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Crimson\MachShipping\Model\UpsDeliverySaturdaysRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class DeliveryDates
{

    /**
     * Max time to ship same day 2:00PM
     *
     * @var int
     */
    const MAX_TIME_SHIP_SAME_DAY = 15;
    const SATURDAY_INT_DAY = 6;
    const SUNDAY_INT_DAY   = 7;

    protected Config $config;
    protected DateTime $dateTime;
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    protected UpsDeliverySaturdaysRepository $upsDeliverySaturdaysRepository;

    public function __construct(
        DateTime $dateTime,
        Config $config,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        UpsDeliverySaturdaysRepository $upsDeliverySaturdaysRepository
    )
    {
        $this->config = $config;
        $this->dateTime = $dateTime;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->upsDeliverySaturdaysRepository = $upsDeliverySaturdaysRepository;
    }

    /**
     * @param int $daysInTransit
     * @param \DateTime $currentDate
     * @param $accountForHolidays
     * @param $accountForWeekends
     * @return bool
     */
    public function checkIfCurrentDayCounts(int $daysInTransit,
                                    \DateTime $currentDate,
                                    $accountForHolidays,
                                    $accountForWeekends
    ): bool
    {
        $isTodayNonBusinessDay = $this->isCurrentDayAHolidayOrWeekend($currentDate, $accountForHolidays, $accountForWeekends);
        $justHour = $currentDate->format('H');
        if($justHour < self::MAX_TIME_SHIP_SAME_DAY && !$isTodayNonBusinessDay) {
            return true;
        }

        return false;
    }

    /**
     * @param \DateTime $startDate
     * @param int $dayInTransit
     * @param $accountForHolidays
     * @param $accountForWeekends
     * @return \DateTime
     */
    public function getDeliveryDay(\DateTime $startDate,
                                         int $dayInTransit,
                                             $accountForHolidays,
                                             $accountForWeekends
    ): \DateTime
    {
        $holidaysList = $this->getNonDeliveryHolidays($startDate);
        $walkAndDeliveryDay  = clone $startDate;
        $businessDaysCount = 0;
        while ($businessDaysCount < $dayInTransit) {
            if($accountForHolidays && count($holidaysList) && in_array($walkAndDeliveryDay->format("Y-m-d"), $holidaysList)) {
                //non-business day, therefore next day
                $walkAndDeliveryDay->modify('+1 day');
                continue;
            }

            if ($accountForWeekends && $walkAndDeliveryDay->format("N") == self::SATURDAY_INT_DAY) {
                //non-business day or not a UPS Saturday for delivery, therefore next day
                $walkAndDeliveryDay->modify('+1 day');
                continue;
            } elseif ($accountForWeekends && $walkAndDeliveryDay->format("N") == self::SUNDAY_INT_DAY) {
                //non-business day, therefore next day
                $walkAndDeliveryDay->modify('+1 day');
                continue;
            }

            //found a business day that meets condition
            $businessDaysCount++;
            if ($businessDaysCount == $dayInTransit) {
                //we got the days, we NEED this date, so break
                break;
            }

            $walkAndDeliveryDay->modify('+1 day');
        }

        return $walkAndDeliveryDay;
    }

    /**
     * @param \DateTime $currentDate
     * @param $accountForHolidays
     * @param $accountForWeekends
     * @return bool
     */
    public function isCurrentDayAHolidayOrWeekend(\DateTime $currentDate,
                                                $accountForHolidays,
                                                $accountForWeekends
    ): bool
    {
        $holidaysList = $this->getNonDeliveryHolidays($currentDate);
        if($accountForHolidays && count($holidaysList)) {
            if (in_array($currentDate->format("Y-m-d"), $holidaysList)) {
                return true;
            }
        }

        if ($accountForWeekends &&
            $currentDate->format('N') == self::SATURDAY_INT_DAY ||
            $currentDate->format('N') == self::SUNDAY_INT_DAY
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param \DateTime $startDate
     * @return array
     */
    protected function getNonDeliveryHolidays(\DateTime $startDate): array
    {
        $dates = [];
        $datesAdmin = explode(',', $this->config->getNonDeliveryHolidays());
        foreach($datesAdmin as $date) {
            $arrayDateMonth = explode('/', $date);
            if ($arrayDateMonth != false
                && ((int)$arrayDateMonth[0] > 0 && (int)$arrayDateMonth[0] <= 12)
                && ((int)$arrayDateMonth[1] > 0 && (int)$arrayDateMonth[1] <= 31)
            ) {
                $currentYear = $startDate->format('Y');
                //validating MM/DD format for Holidays added in Admin
                $dates[] = $this->dateTime->date(
                    $this->_getDayMonthRightFormat($currentYear, (int)$arrayDateMonth[0], (int)$arrayDateMonth[1])
                );

                $currentMonth =  $startDate->format('m');
                if($currentMonth == 12 && (int)$arrayDateMonth[0] == 1){
                    $currentYear++;
                    $dates[] = $this->dateTime->date(
                        $this->_getDayMonthRightFormat($currentYear, (int) $arrayDateMonth[0], (int) $arrayDateMonth[1])
                    );
                }
            }
        }

        return $dates;
    }

    /**
     * @param $currentYear
     * @param int $month
     * @param int $day
     * @return string
     */
    protected function _getDayMonthRightFormat($currentYear, int $month, int $day): string
    {
        $monthFormatted = $month;
        if ($month < 10) {
            $monthFormatted = sprintf('0%d', $month);
        }

        $dayFormatted = $day;
        if ($day < 10) {
            $dayFormatted = sprintf('0%d', $day);
        }

        return $currentYear . '-' . $monthFormatted . '-' . $dayFormatted;
    }

    /**
     * @param $zipCode
     * @return bool
     */
    public function isUpsSaturday($zipCode): bool
    {
        if (!$zipCode) {
            return false;
        }

        $this->searchCriteriaBuilder->addFilter('zip_code_value', $zipCode);
        $zipCodesSearch = $this->upsDeliverySaturdaysRepository->getList($this->searchCriteriaBuilder->create());
        if (!$zipCodesSearch->getTotalCount()) {
            return false;
        }

        return true;
    }
}
