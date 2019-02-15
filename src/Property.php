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

    public function __construct(string $name, string $value, bool $isBinding)
    {
        $this->name = $name;
        $this->value = $value;
        $this->isBinding = $isBinding;
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

}
