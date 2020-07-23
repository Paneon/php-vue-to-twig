<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Models;

use Exception;
use Ramsey\Uuid\Uuid;

class Slot
{
    public const SLOT_DEFAULT_NAME = 'default';

    public const SLOT_PREFIX = 'slot_';

    public const SLOT_VALUE_SUFFIX = '_value';

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $isBinding;

    /**
     * Slot constructor.
     *
     * @throws Exception
     */
    public function __construct(string $name, string $value)
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->name = $name;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getSlotPropertyName(): string
    {
        return self::SLOT_PREFIX . $this->name;
    }

    public function getSlotValueName(): string
    {
        return self::SLOT_PREFIX . $this->name . self::SLOT_VALUE_SUFFIX;
    }

    public function getSlotContentVariableString(): string
    {
        return '__SLOT_' . $this->uuid . '__';
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
