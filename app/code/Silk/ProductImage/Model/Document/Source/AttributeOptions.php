<?php
namespace Silk\ProductImage\Model\Document\Source;

class AttributeOptions implements \Magento\Framework\Option\ArrayInterface
{
    protected $eavAttribute;
    protected $entity;

    public function __construct(
       \Magento\Eav\Model\Attribute $eavAttribute,
       \Magento\Eav\Model\Entity $entity
    )
    {
        $this->eavAttribute = $eavAttribute;
        $this->entity = $entity;
    }

    /**
     * Return labels collection array
     *
     * @param bool|string $label add empty values to result with specific label
     * @return array
     */
    public function getLabelsCollection($label = false)
    {
        $attributeCollection = $this->eavAttribute->getCollection();
        $attributeCollection->addFieldToFilter('is_user_defined', 1);
        $attributeCollection->addFieldToFilter(
            'entity_type_id',
            $this->entity->setType('catalog_product')->getTypeId()
        );
        $options = [];
        foreach ($attributeCollection as $attribute) {
            $options[] = [ 'value' => $attribute->getAttributeCode(), 'label' => $attribute->getFrontendLabel() ];
        }
        usort($options, function($a,$b){ return (strcmp($a['value'],$b['value']));});
        if ($label) {
            array_unshift($options, ['value' => '', 'label' => $label]);
        }
        return $options;
    }


    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getLabelsCollection((string)new \Magento\Framework\Phrase('-- select attribute --'));
    }
}




 ?>