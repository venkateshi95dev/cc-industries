<?php

namespace Crimson\CorvetteCentral\Model\Customer\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class CorvetteYears extends AbstractSource
{
    const INITIAL_YEAR = 1953;

    public function getAllOptions()
    {
        if ($this->_options === null) {
            $currentYear = (int)date('Y');
            $years = range($currentYear, self::INITIAL_YEAR); // descending

            $this->_options = [];
            foreach ($years as $year) {
                $this->_options[] = [
                    'label' => (string)$year,
                    'value' => (string)$year,
                ];
            }
        }
        return $this->_options;
    }
}
