<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        5/15/2019 9:47 AM
 * @brief
 */

namespace Crimson\Brand\Ui\Component\Result\Listing;

use Magento\Framework\View\Element\UiComponent\ContextInterface;

class MassAction extends \Magento\Ui\Component\MassAction
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    public function __construct(
        ContextInterface $context,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->authorization = $authorization;
    }

    /**
     * Prepare component configuration
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        if (!$this->_isAllowed()) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }

    protected function _isAllowed()
    {
        return $this->authorization->isAllowed('Crimson_Brand::edit');
    }
}