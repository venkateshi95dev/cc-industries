<?php
declare(strict_types=1);

namespace Crimson\Catalog\Plugin\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front as MagentoFront;
use Magento\Framework\App\ResourceConnection;
use Closure;

class Front
{
    public function __construct(
        protected ResourceConnection $resource
    ) {
    }

    /**
     * @param MagentoFront $subject
     * @param Closure $proceed
     * @return string
     */
    public function aroundGetFormHtml(
        MagentoFront $subject,
        Closure $proceed
    ) : string
    {
        $form = $subject->getForm();
        $fieldset = $form->getElement('front_fieldset');
        $fieldset->addField(
            'compare_position',
            'text',
            [
                'name' => 'compare_position',
                'sortOrder' => 31,
                'label' => __('Compare Position'),
                'title' => __('Compare Position'),
                'note' => __('Used to sort comparable attributes on the storefront.'),
                'class' => 'validate-digits'
            ]
        );


        $request = $subject->getRequest();
        $attributeId = (int) ($request->getParam('attribute_id') ?? $request->getParam('id') ?? 0);

        if ($attributeId && $attributeId > 0) {
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName('catalog_eav_attribute');

            $value = $connection->fetchOne(
                $connection->select()
                    ->from($table, ['compare_position'])
                    ->where('attribute_id = ?', $attributeId)
            );

            if ($value) {
                $elem = $form->getElement('compare_position');
                if ($elem) {
                    $elem->setValue((int) $value);
                }
            }
        }


        return $proceed();
    }
}
