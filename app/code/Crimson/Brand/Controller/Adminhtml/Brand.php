<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/10/2019
 */
namespace Crimson\Brand\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Crimson\Brand\Api\BrandRepositoryInterface;

abstract class Brand extends Action
{
    /**
     * @var string
     */
    const ADMIN_RESOURCE = 'Crimson_Brand::view';

    /**
     * Brand repository
     *
     * @var BrandRepositoryInterface
     */
    protected $brandRepository;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Result Page Factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Date filter
     *
     * @var Date
     */
    protected $dateFilter;

    /**
     * Brand constructor.
     *
     * @param Registry $registry
     * @param BrandRepositoryInterface $brandRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        BrandRepositoryInterface $brandRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context
    ) {
        parent::__construct($context);
        $this->coreRegistry         = $registry;
        $this->brandRepository      = $brandRepository;
        $this->resultPageFactory    = $resultPageFactory;
        $this->dateFilter           = $dateFilter;
    }
}
