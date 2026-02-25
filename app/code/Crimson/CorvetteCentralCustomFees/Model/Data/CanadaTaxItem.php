<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentralCustomFees\Model\Data;

use Crimson\CorvetteCentralCustomFees\Api\Data\CanadaTaxItemInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class CanadaTaxItem extends AbstractExtensibleObject implements CanadaTaxItemInterface
{
    public function getCode(): ?string
    {
        return $this->_get(self::CODE);
    }

    public function setCode(?string $code): CanadaTaxItemInterface
    {
        return $this->setData(self::CODE, $code);
    }

    public function getValue(): ?string
    {
        return $this->_get(self::VALUE);
    }

    public function setValue(?string $value): CanadaTaxItemInterface
    {
        return $this->setData(self::VALUE, $value);
    }
}
