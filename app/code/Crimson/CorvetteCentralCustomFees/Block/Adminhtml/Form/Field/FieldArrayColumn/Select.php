<?php

namespace Crimson\CorvetteCentralCustomFees\Block\Adminhtml\Form\Field\FieldArrayColumn;

class Select extends AbstractColumn
{
    protected $_template = 'Crimson_CorvetteCentralCustomFees::form/field/field-array-column/select.phtml';

    public function setOptions(array $options)
    {
        $this->setData('options', $options);

        return $this;
    }

    public function getOptions(): array
    {
        return $this->getData('options') ?? [];
    }
}
