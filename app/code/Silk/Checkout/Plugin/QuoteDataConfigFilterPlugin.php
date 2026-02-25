<?php
namespace Silk\Checkout\Plugin;

class QuoteDataConfigFilterPlugin 
{
	/**
	 * @var \Silk\Checkout\Data
	 */
	protected $helper;

	public function __construct(
		\Silk\Checkout\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }

	public function beforeProcess(\Magento\Captcha\Model\Filter\QuoteDataConfigFilter $subject, $config)
	{
		$config['quoteData']['express_shipping_methods'] = $this->helper->getExpressShippingMethods();

		 if($this->helper->IsAlertEnabled() && preg_match("/\"backorders\":/", json_encode($config['quoteItemData']))){
            $config['quoteData']['backorders'] = 1;
            $config['quoteData']['shipping_notice_for_backorder_express'] = $this->helper->getAlertCopy();			
         }
		 return [$config];
	}

}