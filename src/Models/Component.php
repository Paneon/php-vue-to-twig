<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Models;

use Exception;

class Component
{
    /**
     * @var string[]
     */
    protected $components = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var Property[]
     */
    protected $properties = [];

    /**
     * @var Slot[]
     */
    protected $slots = [];

    /**
     * Component constructor.
     *
     * @param string $name
     * @param string $path
     */
    public function __construct(string $name = '', string $path = '')
    {
        $this->name = $name;
        $this->path = $path;
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
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param string $name
     * @param string $path
     */
    public function registerComponents(string $name, string $path): void
    {
        $this->components[$name] = $path;
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool   $isBinding
     */
    public function addProperty(string $name, string $value, bool $isBinding = false): void
    {
        $this->properties[] = new Property(
            $this->kebabToCamelCase($name),
            $value,
            $isBinding
        );
    }

    /**
     * @param string $value
     *
     * @throws Exception
     *
     * @return Slot
     */
    public function addDefaultSlot(string $value): Slot
    {
        return $this->addSlot(Slot::SLOT_DEFAULT_NAME, $value);
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @throws Exception
     *
     * @return Slot
     */
    public function addSlot(string $name, string $value): Slot
    {
        $this->slots[$name] = new Slot($name, $value);

        $this->properties[] = new Property(
            $this->slots[$name]->getSlotPropertyName(),
            $this->slots[$name]->getSlotValueName(),
            true
        );

        return $this->slots[$name];
    }

    /**
     * @param string $string
     * @param bool   $capitalizeFirstCharacter
     *
     * @return string
     */
    public function kebabToCamelCase(string $string, bool $capitalizeFirstCharacter = false): string
    {
        $str = str_replace('-', '', ucwords($string, '-'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }

    /**
     * @return bool
     */
    public function hasSlots(): bool
    {
        return !empty($this->slots);
    }

    /**
     * @return Slot[]
     */
    public function getSlots(): array
    {
        return $this->slots;
    }
}
