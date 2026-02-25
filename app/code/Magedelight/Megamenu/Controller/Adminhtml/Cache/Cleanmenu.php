<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace  Magedelight\Megamenu\Controller\Adminhtml\Cache;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magedelight\Megamenu\Helper\Cache;

class Cleanmenu extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magedelight_Megamenu::cleanmenu';

    /**
     * @var Cache
     */
    protected $cacheHelper;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Cache $cacheHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Cache $cacheHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory= $resultJsonFactory;
        $this->cacheHelper = $cacheHelper;
    }

    /**
     * Index action
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultJson = $this->resultJsonFactory->create();
        $sendData = [];
        try {
            if ($params['type'] == 'storeMenu') {
                $this->cacheHelper->updateVariableByCode($this->cacheHelper->getStoreMenuKey());
            }
            $sendData = [
                'messages' => 'Successfully.',
                'error' => false
            ];
        } catch (\Exception $e) {
            $sendData = [
                'messages' => 'Please try again.',
                'error' => true
            ];
        }
        return $resultJson->setData($sendData);
    }
}
