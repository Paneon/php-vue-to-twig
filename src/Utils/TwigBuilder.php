<?php

namespace Paneon\VueToTwig\Utils;

use Paneon\VueToTwig\Property;

class TwigBuilder
{
    protected const OPEN = 0;
    protected const CLOSE = 1;

    protected $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'tag_comment' => ['{#', '#}'],
            'tag_block' => ['{%', '%}'],
            'tag_variable' => ['{{', '}}'],
            'whitespace_trim' => '-',
            'interpolation' => ['#{', '}'],
        ], $options);
    }

    public function createSet($name)
    {
        return $this->createBlock('set '.$name);
    }

    public function closeSet()
    {
        return $this->createBlock('endset');
    }

    public function createVariable($name, $assignment)
    {
        return $this->createBlock('set ' . $name . ' = ' . $assignment);
    }

    public function createMultilineVariable($name, $assignment)
    {
        return $this->createBlock('set ' . $name)
            . $assignment
            . $this->createBlock('endset');
    }

    public function createIf(string $condition)
    {
        $condition = $this->refactorCondition($condition);

        return $this->createBlock('if ' . $condition);
    }

    public function createElseIf(string $condition)
    {
        $condition = $this->refactorCondition($condition);

        return $this->createBlock('elseif ' . $condition);
    }

    public function createElse()
    {
        return $this->createBlock('else');
    }

    public function createEndIf()
    {
        return $this->createBlock('endif');
    }

    public function createForItemInList(string $item, string $list)
    {
        return $this->createBlock('for ' . $item . ' in ' . $list);
    }

    public function createForKeyInList(string $key, string $list)
    {
        return $this->createBlock('for ' . $key . ' in ' . $list);
    }

    public function createFor(string $list, ?string $item = null, ?string $key = null)
    {
        if ($item !== null && $key !== null) {
            return $this->createBlock('for ' . $key . ', ' . $item . ' in ' . $list);
        } elseif ($item !== null) {
            return $this->createForItemInList($item, $list);
        } elseif ($key !== null) {
            return $this->createForKeyInList($key, $list);
        }

        return null;
    }

    public function createEndFor()
    {
        return $this->createBlock('endfor');
    }

    public function createComment(string $comment)
    {
        return $this->options['tag_comment'][self::OPEN] . ' ' . $comment . ' ' . $this->options['tag_comment'][self::CLOSE];
    }

    public function createMultilineComment(array $comments)
    {
        return $this->options['tag_comment'][self::OPEN] . ' ' . $comments . ' ' . $this->options['tag_comment'][self::CLOSE];
    }

    public function createBlock($content)
    {
        return "\n" . $this->options['tag_block'][self::OPEN] . ' ' . $content . ' ' . $this->options['tag_block'][self::CLOSE];
    }

    /**
     * @param string     $partialPath
     * @param Property[] $variables
     *
     * @return string
     */
    public function createIncludePartial(string $partialPath, array $variables = [])
    {
        if (empty($variables)) {
            return $this->createBlock('include "' . $partialPath . '"');
        }

        $serializedProperties = $this->serializeComponentProperties($variables);

        return $this->createBlock('include "' . $partialPath . '" with ' . $serializedProperties);
    }

    /**
     * @param Property[] $properties
     *
     * @return string
     */
    public function serializeComponentProperties(array $properties): string
    {
        $props = [];

        /** @var Property $property */
        foreach ($properties as $property) {
            if ($property->getName() === 'key') {
                continue;
            }

            $props[] = '\'' . $property->getName() . '\'' . ': ' . $property->getValue();
        }

        return '{ ' . implode(', ', $props) . ' }';
    }

    public function refactorCondition(string $condition): string
    {
        $condition = str_replace('===', '==', $condition);
        $condition = str_replace('!==', '!=', $condition);

        return $condition;
    }

    public function createVariableOutput($varName): string
    {
        return '{{ '.$varName.' }}';
    }
}
