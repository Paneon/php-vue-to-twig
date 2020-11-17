<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Models;

class Property
{
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
     * @var bool
     */
    protected $isRequired;

    /**
     * @var string|null
     */
    protected $default;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * Property constructor.
     */
    public function __construct(string $name, string $value, bool $isBinding)
    {
        $this->name = $name;
        $this->value = $value;
        $this->isBinding = $isBinding;
        $this->isRequired = false;
        $this->default = null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): Property
    {
        $this->value = $value;
        return $this;
    }

    public function isBinding(): bool
    {
        return $this->isBinding;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): void
    {
        $this->isRequired = $isRequired;
    }

    /**
     * @return string|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    public function hasDefault(): bool
    {
        return $this->default !== null;
    }

    public function setDefault(string $default): void
    {
        $this->default = $default;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }
}
