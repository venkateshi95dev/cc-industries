<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magedelight\Megamenu\Api\MegamenuManagementInterface;

class PrimaryMegamenu implements ResolverInterface
{
    /**
     * @var MegamenuManagementInterface
     */
    private $megamenuManagement;

    /**
     * PrimaryMegamenu constructor.
     * @param MegamenuManagementInterface $megamenuManagement
     */
    public function __construct(
        MegamenuManagementInterface $megamenuManagement
    ) {
        $this->megamenuManagement = $megamenuManagement;
    }

    /**
     * Resolve
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        ?array      $value = null,
        ?array $args = null
    ) {
        try {
            $menuData = $this->megamenuManagement->getMenuData($context->getUserId());
            return [
                'menu' => $menuData->getMenu()
            ];
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
    }
}
