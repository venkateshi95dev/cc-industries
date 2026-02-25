<?php

namespace Crimson\CorvetteCentral\Block\Product\View\Type;

use Magento\Framework\Phrase;

class Simple extends \Crimson\Catalog\Block\Product\View\Type\Simple
{
    public const ETA_ATTR_CODE = 'cc_ecommerce_lead_days';
    protected const LAST_DATE_LEAD_DAYS_UPDATE_DATE_ATTR_CODE = 'cc_ecomleaddays_lastupdate_date';

    public function getBackorderETAMessage(): Phrase
    {
        $eCommerceLeadDays = (int)$this->getProduct()->getData(self::ETA_ATTR_CODE);
        if (empty($eCommerceLeadDays)) {
            return __($this->config->getETADefaultMsg());
        }

        try {
            $possibleShipDate = $this->_getShipDate($eCommerceLeadDays);
            if ($eCommerceLeadDays > 0 && $eCommerceLeadDays <= 14) {
                return __($this->config->getETAFirstRangeMsg(), $possibleShipDate);
            }

            if ($eCommerceLeadDays >= 15 && $eCommerceLeadDays <= 45) {
                return __($this->config->getETASecondRangeMsg(), $possibleShipDate);
            }

            if ($eCommerceLeadDays > 45 && $eCommerceLeadDays <= 500) {
                return __($this->config->getETAThirdRangeMsg(), $possibleShipDate);
            }

            if ($eCommerceLeadDays > 500) {
                return __($this->config->getETAFourthRangeMsg(), $possibleShipDate);
            }

            return __($this->config->getETADefaultMsg());
        } catch (\Exception $e) {
            return __($this->config->getETADefaultMsg());
        }
    }

    protected function _getShipDate(int $eCommerceLeadDays): string
    {
        $lastDataLeadDaysUpdateDate = $this->getProduct()->getData(self::LAST_DATE_LEAD_DAYS_UPDATE_DATE_ATTR_CODE);
        if (empty($lastDataLeadDaysUpdateDate) || $lastDataLeadDaysUpdateDate === '0001-01-01') {
            $possibleShipDate = $this->_timezone->date()->modify(sprintf('+%s day', $eCommerceLeadDays))->format('F j');
        } else {
            $possibleShipDate = $this->_getLastDateLeadDaysUpdateDateFormatted($lastDataLeadDaysUpdateDate, $eCommerceLeadDays);
            if (!$possibleShipDate) {
                $possibleShipDate = $this->_timezone->date()->modify(sprintf('+%s day', $eCommerceLeadDays))->format('F j');
            }
        }

        return $possibleShipDate;
    }

    private function _getLastDateLeadDaysUpdateDateFormatted(string $lastDataLeadDaysUpdateDate, int $eCommerceLeadDays): string
    {
        try {
            //There were some conversion issues because of the format
            //So first we create a date and then we modify it
            $dateTime = new \DateTime($lastDataLeadDaysUpdateDate);
            return $this->_timezone->scopeDate()->setTimestamp($dateTime->getTimestamp())->modify(sprintf('+%s day', $eCommerceLeadDays))->format('F j');
        } catch (\Exception $e) {
            return '';
        }
    }
}
