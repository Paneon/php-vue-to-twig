<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Models;

use Exception;
use Ramsey\Uuid\Uuid;

class Concat
{
    public const CONCAT_REGEX = '/__CONCAT_[a-f0-9]{8}_[a-f0-9]{4}_[a-f0-9]{4}_[a-f0-9]{4}_[a-f0-9]{12}__/';

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $isNumeric;

    /**
     * Slot constructor.
     *
     * @throws Exception
     */
    public function __construct(string $value, bool $isNumeric)
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->value = $value;
        $this->isNumeric = $isNumeric;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function isNumeric(): bool
    {
        return $this->isNumeric;
    }

    public function setIsNumeric(bool $isNumeric): void
    {
        $this->isNumeric = $isNumeric;
    }

    public function getConcatContentVariableString(): string
    {
        return '__CONCAT_' . str_replace('-', '_', $this->uuid) . '__';
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
