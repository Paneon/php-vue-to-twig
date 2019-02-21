<?php

namespace Paneon\VueToTwig;

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

    protected $isRequired;

    protected $default;

    public function __construct(string $name, string $value, bool $isBinding)
    {
        $this->name = $name;
        $this->value = $value;
        $this->isBinding = $isBinding;
        $this->isRequired = false;
        $this->default = null;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isBinding(): bool
    {
        return $this->isBinding;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * @param bool $isRequired
     */
    public function setIsRequired(bool $isRequired): void
    {
        $this->isRequired = $isRequired;
    }

    /**
     * @return null
     */
    public function getDefault()
    {
        return $this->default;
    }

    public function hasDefault(): bool
    {
        return $this->default !== null;
    }

    /**
     * @param null $default
     */
    public function setDefault($default): void
    {
        $this->default = $default;
    }

}
