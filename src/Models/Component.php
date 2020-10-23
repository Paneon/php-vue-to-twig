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
     */
    public function __construct(string $name = '', string $path = '')
    {
        $this->name = $name;
        $this->path = $path;
    }

    public function getName(): string
    {
        return $this->name;
    }

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

    public function registerComponents(string $name, string $path): void
    {
        $this->components[$name] = $path;
    }

    public function addProperty(string $name, string $value, bool $isBinding = false): void
    {
        $this->properties[] = new Property(
            $this->kebabToCamelCase($name),
            $value,
            $isBinding
        );
    }

    /**
     * @throws Exception
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

    public function addEmptyDefaultSlot(): void
    {
        $this->properties[] = new Property(Slot::SLOT_PREFIX . Slot::SLOT_DEFAULT_NAME, '""', false);
    }

    public function kebabToCamelCase(string $string, bool $capitalizeFirstCharacter = false): string
    {
        $str = str_replace('-', '', ucwords($string, '-'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }

    public function hasSlots(): bool
    {
        return !empty($this->slots);
    }

    public function hasSlot(string $name): bool
    {
        return !empty($this->slots[$name]);
    }

    /**
     * @return Slot[]
     */
    public function getSlots(): array
    {
        return $this->slots;
    }
}
