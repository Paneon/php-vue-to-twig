<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Models;

use Exception;
use Ramsey\Uuid\Uuid;

class Pre
{
    public const PRE_REGEX = '/__PRE_[a-f0-9]{8}_[a-f0-9]{4}_[a-f0-9]{4}_[a-f0-9]{4}_[a-f0-9]{12}__/';

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $value;

    /**
     * Slot constructor.
     *
     * @throws Exception
     */
    public function __construct(string $value)
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getPreContentVariableString(): string
    {
        return '__PRE_' . str_replace('-', '_', $this->uuid) . '__';
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
