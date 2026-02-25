<?php

namespace Crimson\CorvetteCentralCustomFees\Block\Adminhtml\Form\Field\FieldArrayColumn;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class AbstractColumn extends Template
{
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }

    public function setInputName(?string $name)
    {
        $this->setData('input_name', $name);

        return $this;
    }

    public function getInputName(): ?string
    {
        return $this->getData('input_name');
    }

    public function setInputId(?string $id)
    {
        $this->setData('input_id', $id);

        return $this;
    }

    public function getInputId(): ?string
    {
        return $this->getData('input_id');
    }

    public function setColumnName(?string $name)
    {
        $this->setData('column_name', $name);

        return $this;
    }

    public function getColumnName(): ?string
    {
        return $this->getData('column_name');
    }

    public function setColumn(?array $column)
    {
        $this->setData('column', $column);

        return $this;
    }

    public function getColumn(): ?array
    {
        return $this->getData('column');
    }

    public function renderEscapedHtmlAttr(string $attributeData): string
    {
        // Any prototype JS templates in the attribute must not be escaped or they will not render correctly when the field array is rendered
        if (!preg_match_all('#(<%-.*?%>)#', $attributeData, $matches, PREG_OFFSET_CAPTURE)) {
            return $this->_escaper->escapeHtmlAttr($attributeData);
        }


        $lastMatchEndOffset = 0;

        $escapedAttributeData = '';
        foreach ($matches[1] as $match) {
            $escapedAttributeData .= $this->_escaper->escapeHtmlAttr(
                substr($attributeData, $lastMatchEndOffset, $match[1] - $lastMatchEndOffset)
            );
            $escapedAttributeData .= $match[0];

            $lastMatchEndOffset = $match[1] + strlen($match[0]);
        }

        $escapedAttributeData .= $this->_escaper->escapeHtmlAttr(
            substr($attributeData, $lastMatchEndOffset)
        );

        return $escapedAttributeData;
    }
}
