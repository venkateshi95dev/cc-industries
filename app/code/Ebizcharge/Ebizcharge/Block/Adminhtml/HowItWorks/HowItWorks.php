<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\HowItWorks;

use Ebizcharge\Ebizcharge\Api\Data\HowItWorksInterface;
use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * How It Works block class
 *
 * Class HowItWorks
 */
class HowItWorks extends Template
{
    /**
     * @var Http
     */
    protected $_request;

    /**
     * @param Template\Context $context
     * @param Http $request
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Template\Context $context,
        Http $request,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        parent::__construct(
            $context,
            $data,
            $jsonHelper,
            $directoryHelper
        );
        $this->_request = $request;
    }

    /**
     * Get current page full action name
     *
     * @return string
     */
    public function getPageFullActionName(): string
    {
        return $this->_request->getFullActionName();
    }

    /**
     * How it works logo
     *
     * @return string
     */
    public function getHowItWorksLogo(): string
    {
        return $this->getViewFileUrl(HowItWorksInterface::EBIZ_HOW_IT_WORKS_LOGO);
    }

    /**
     * How it works tooltip video logo
     *
     * @return string
     */
    public function getHowItWorksVideoPlayLogo(): string
    {
        return $this->getViewFileUrl(HowItWorksInterface::EBIZ_HOW_IT_WORKS_VIDEO_PLAY_LOGO);
    }

    /**
     * Get videos links
     *
     * @return array|array[]
     */
    public function getVideosLinks()
    {
        $allVideos = HowItWorksInterface::EBIZ_HOW_IT_WORKS_VIDEO_LABEL_LINKS;
        $actionName = $this->getPageFullActionName();
        return $allVideos[$actionName] ?? [];
    }
}
