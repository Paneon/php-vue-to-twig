<?php declare(strict_types=1);

namespace Paneon\VueToTwig;


use DOMDocument;
use Exception;

class Component
{
    /** @var String[] */
    protected $components = [];
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $path;

    /** @var Property[] */
    protected $properties = [];

    public function __construct(string $name = '', string $path = '')
    {
        $this->name = $name;
        $this->path = $path;
    }

    public function getName(){
        return $this->name;
    }

    public function getPath(){
        return $this->path;
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function registerComponents(string $name, string $path)
    {
        $this->components[$name] = $path;
    }

    public function addProperty(string $name, string $value, bool $isBinding = false) {
        $this->properties[] = new Property(
            $this->kebabToCamelCase($name),
            $value,
            $isBinding
        );
    }

    public function kebabToCamelCase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace('-', '', ucwords($string, '-'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }
}
