<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Plugin\Block\Info;

/**
 * Class Checkmo for adding check number.
 */
class Checkmo
{
    /**
     * Before to html
     *
     * @param \Magento\OfflinePayments\Block\Info\Checkmo $subject
     */
    public function beforeToHtml(\Magento\OfflinePayments\Block\Info\Checkmo $subject)
    {
        $template = $subject->getTemplate();
        if ($template === 'Magento_OfflinePayments::info/checkmo.phtml') {
            $subject->setTemplate('I95DevConnect_MessageQueue::info/checkmo.phtml');
        }
    }
}
