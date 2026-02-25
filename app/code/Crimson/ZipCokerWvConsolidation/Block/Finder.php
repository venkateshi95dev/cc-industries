<?php
namespace Crimson\ZipCokerWvConsolidation\Block;

use Amasty\Finder\Api\FinderRepositoryInterface;
use Amasty\Finder\Model\ConfigProvider;
use Amasty\Finder\Model\Dropdown;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\Url\Encoder;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class Finder extends \Amasty\Finder\Block\Form
{

	const RADIAL = 'finderattribute/general/radial';
	const BIASPLY = 'finderattribute/general/biasply';
	const BIASLOOKRADIAL = 'finderattribute/general/biaslookradial';

    private $finderRepository;

    public function __construct(
        private readonly Config                      $config,
        private readonly ScopeConfigInterface        $scopeConfig,
        Context                                      $context,
        Registry                                     $registry,
        EncoderInterface     $jsonEncoder,
        Resolver        $layerResolver,
        Encoder               $urlEncoder,
        FinderRepositoryInterface $finderRepository,
        ConfigProvider          $configHelper,
        Dropdown                $dropdownModel,
        array                                        $data = []
    ) {
        parent::__construct($context, $registry, $jsonEncoder, $layerResolver, $urlEncoder,$finderRepository,$configHelper,$dropdownModel,$data);
        $this->finderRepository = $finderRepository;
    }

    function getAttributeOptionValues($arg_attribute) {
        $attribute = $this->config->getAttribute('catalog_product',$arg_attribute);
        $options = $attribute->getSource()->getAllOptions();
        $optionsList = '';
        foreach($options as $option) {
            $value = $attribute->getSource()->getOptionId($option['label']);
            if($value){
                $optionsList .= '<option value="'.$value.'">'.$option['label'].'</option>';
            }
        }
        return $optionsList;
    }

    public function getRadial()
    {
        return $this->scopeConfig->getValue(self::RADIAL, ScopeInterface::SCOPE_STORE);
    }

    public function getBiasply()
    {
        return $this->scopeConfig->getValue(self::BIASPLY, ScopeInterface::SCOPE_STORE);
    }

    public function getBiaslookradial()
    {
        return $this->scopeConfig->getValue(self::BIASLOOKRADIAL, ScopeInterface::SCOPE_STORE);
    }

    public function getSize()
    {
        $id = $this->getRequest()->getParam('finder_id');
        $finder = $this->finderRepository->getById($id);

        return $finder->getName();
    }
}
