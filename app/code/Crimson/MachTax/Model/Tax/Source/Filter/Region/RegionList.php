<?php

namespace Crimson\MachTax\Model\Tax\Source\Filter\Region;

use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Api\Data\RegionInformationInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class RegionList
 * @package Crimson\MachTax\Model\Tax\Source\Filter\Region
 */
class RegionList implements OptionSourceInterface
{

    protected array $_options = [];

    public function __construct(
        protected CountryInformationAcquirerInterface $country
    ) {}

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function toOptionArray(): array
    {
        if (empty($this->_options)) {
            $countries = array('US', 'CA');

            $this->_options[] = array(
                'label' => '',
                'value' => ''
            );

            foreach ($countries as $country) {

                /** @var \Magento\Directory\Api\Data\CountryInformationInterface $countryInfo */
                $countryInfo = $this->country->getCountryInfo($country);

                $availableRegions = $countryInfo->getAvailableRegions();
                if (count($availableRegions)) {

                    $regions = $this->_prepareRegionsArray($availableRegions);
                    if (is_array($regions) && !empty($regions)) {
                        $this->_options[] = array(
                            'label' => $countryInfo->getFullNameLocale(),
                            'value' => $regions
                        );
                    }
                }
            }
        }

        return $this->_options;
    }

    /**
     * @param array $regions
     *
     * @return array
     */
    protected function _prepareRegionsArray(array $regions): array
    {
        $result = [];
        foreach ($regions as $region) {
            /** @var RegionInformationInterface $region */
            $result[] = [
                'title' => $region->getCode(),
                'value' => $region->getId(),
                'label' => $region->getName()
            ];
        }

        return $result;
    }

}
