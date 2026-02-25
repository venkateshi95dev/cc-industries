<?php

namespace Crimson\ProductAlert\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    protected $options;

    public function getStatuses()
    {
        return [
            0 => __('Not Sent'),
            1 => __('Sent'),
        ];
    }

    public function toOptionArray(): array
    {
        if (!$this->options) {
            $this->options = [];
            foreach ($this->getStatuses() as $statusCode => $statusName) {
                $this->options[$statusCode]['label'] = $statusName;
                $this->options[$statusCode]['value'] = $statusCode;
            }
        }
        return $this->options;
    }
}
