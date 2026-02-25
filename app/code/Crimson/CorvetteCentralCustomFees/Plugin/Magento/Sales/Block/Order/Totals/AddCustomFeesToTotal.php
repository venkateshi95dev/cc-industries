<?php

namespace Crimson\CorvetteCentralCustomFees\Plugin\Magento\Sales\Block\Order\Totals;

use Crimson\CorvetteCentralCustomFees\Block\Sales\Totals as CustomFeesTotal;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Block\Order\Totals;
use Throwable;

class AddCustomFeesToTotal
{
    public function __construct(
        protected DataObjectFactory $dataObjectFactory,
        protected State $appState
    ) {
    }



    public function beforeToHtml(Totals $subject)
    {
        $subject->addChild('custom_fees', CustomFeesTotal::class);
    }
}
