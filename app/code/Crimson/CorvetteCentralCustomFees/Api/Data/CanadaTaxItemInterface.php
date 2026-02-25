<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentralCustomFees\Api\Data;

interface CanadaTaxItemInterface
{
    public const CODE = 'code';
    public const VALUE = 'value';

    /**
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * @param string|null $code
     * @return \Crimson\CorvetteCentralCustomFees\Api\Data\CanadaTaxItemInterface
     */
    public function setCode(?string $code): \Crimson\CorvetteCentralCustomFees\Api\Data\CanadaTaxItemInterface;

    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param string|null $value
     * @return \Crimson\CorvetteCentralCustomFees\Api\Data\CanadaTaxItemInterface
     */
    public function setValue(?string $value): \Crimson\CorvetteCentralCustomFees\Api\Data\CanadaTaxItemInterface;
}
