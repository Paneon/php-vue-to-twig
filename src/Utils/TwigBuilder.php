<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Utils;

use Paneon\VueToTwig\Models\Property;
use Paneon\VueToTwig\Models\Replacements;
use ReflectionException;

class TwigBuilder
{
    protected const OPEN = 0;

    protected const CLOSE = 1;

    /**
     * @var mixed[]
     */
    protected $options;

    /**
     * TwigBuilder constructor.
     *
     * @param mixed[] $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge(
            [
                'tag_comment' => ['{#', '#}'],
                'tag_block' => ['{%', '%}'],
                'tag_variable' => ['{{', '}}'],
                'whitespace_trim' => '-',
                'interpolation' => ['#{', '}'],
            ],
            $options
        );
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function createSet(string $name): string
    {
        return $this->createBlock('set ' . $name);
    }

    /**
     * @return string
     */
    public function closeSet(): string
    {
        return $this->createBlock('endset');
    }

    /**
     * @param string $name
     * @param string $assignment
     *
     * @return string
     */
    public function createVariable(string $name, string $assignment): string
    {
        return $this->createBlock('set ' . $name . ' = ' . $assignment);
    }

    /**
     * @param string $name
     * @param string $defaultValue
     *
     * @return string
     */
    public function createDefaultForVariable(string $name, string $defaultValue): string
    {
        return $this->createBlock('set ' . $name . ' = ' . $name . '|default(' . $defaultValue . ')');
    }

    /**
     * @param string $name
     * @param string $assignment
     *
     * @return string
     */
    public function createMultilineVariable(string $name, string $assignment): string
    {
        return $this->createBlock('set ' . $name)
            . $assignment
            . $this->createBlock('endset');
    }

    /**
     * @param string $condition
     *
     * @throws ReflectionException
     *
     * @return string
     */
    public function createIf(string $condition): string
    {
        $condition = $this->refactorCondition($condition);

        return $this->createBlock('if ' . $condition);
    }

    /**
     * @param string $condition
     *
     * @throws ReflectionException
     *
     * @return string
     */
    public function createElseIf(string $condition): string
    {
        $condition = $this->refactorCondition($condition);

        return $this->createBlock('elseif ' . $condition);
    }

    /**
     * @return string
     */
    public function createElse(): string
    {
        return $this->createBlock('else');
    }

    /**
     * @return string
     */
    public function createEndIf(): string
    {
        return $this->createBlock('endif');
    }

    /**
     * @param string $item
     * @param string $list
     *
     * @return string
     */
    public function createForItemInList(string $item, string $list): string
    {
        return $this->createBlock('for ' . $item . ' in ' . $list);
    }

    /**
     * @param string $key
     * @param string $list
     *
     * @return string
     */
    public function createForKeyInList(string $key, string $list): string
    {
        return $this->createBlock('for ' . $key . ' in ' . $list);
    }

    /**
     * @param string      $list
     * @param string|null $item
     * @param string|null $key
     *
     * @return string|null
     */
    public function createFor(string $list, ?string $item = null, ?string $key = null): ?string
    {
        if ($item !== null && $key !== null) {
            return $this->createBlock('for ' . $key . ', ' . $item . ' in ' . $list);
        }
        if ($item !== null) {
            return $this->createForItemInList($item, $list);
        }
        if ($key !== null) {
            return $this->createForKeyInList($key, $list);
        }

        return null;
    }

    /**
     * @return string
     */
    public function createEndFor(): string
    {
        return $this->createBlock('endfor');
    }

    /**
     * @param string $comment
     *
     * @return string
     */
    public function createComment(string $comment): string
    {
        return $this->options['tag_comment'][self::OPEN] . ' ' . $comment . ' ' . $this->options['tag_comment'][self::CLOSE];
    }

    /**
     * @param string[] $comments
     *
     * @return string
     */
    public function createMultilineComment(array $comments): string
    {
        return $this->options['tag_comment'][self::OPEN] . ' ' . implode(
            "\n",
            $comments
        ) . ' ' . $this->options['tag_comment'][self::CLOSE];
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function createBlock(string $content): string
    {
        return "\n" . $this->options['tag_block'][self::OPEN] . ' ' . $content . ' ' . $this->options['tag_block'][self::CLOSE];
    }

    /**
     * @param string     $partialPath
     * @param Property[] $variables
     *
     * @return string
     */
    public function createIncludePartial(string $partialPath, array $variables = []): string
    {
        $hasClassProperty = false;
        foreach ($variables as $variable) {
            if ($variable->getName() === 'class') {
                $hasClassProperty = true;
            }
        }

        if (!$hasClassProperty) {
            $variables[] = new Property('class', '""', false);
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

    /**
     * @param string $value
     *
     * @return string
     */
    public function sanitizeAttributeValue(string $value): string
    {
        $value = Replacements::sanitizeSingleReplacement($value, Replacements::PIPE);

        return $value;
    }

    /**
     * @param string $condition
     *
     * @throws ReflectionException
     *
     * @return string
     */
    public function refactorCondition(string $condition): string
    {
        $refactoredCondition = '';
        $charsCount = mb_strlen($condition, 'UTF-8');
        $quoteChar = null;
        $lastChar = null;
        $buffer = '';

        for ($i = 0; $i < $charsCount; ++$i) {
            $char = mb_substr($condition, $i, 1, 'UTF-8');
            if ($quoteChar === null && ($char === '"' || $char === '\'')) {
                $quoteChar = $char;
                if ($buffer !== '') {
                    $refactoredCondition .= $this->refactorConditionPart($buffer);
                    $buffer = '';
                }
                $refactoredCondition .= $char;
            } elseif ($quoteChar === $char && $lastChar !== '\\') {
                $quoteChar = null;
                $refactoredCondition .= $char;
            } else {
                if ($quoteChar === null) {
                    $buffer .= $char;
                } else {
                    $refactoredCondition .= $char;
                }
            }
            $lastChar = $char;
        }
        if ($buffer !== '') {
            $refactoredCondition .= $this->refactorConditionPart($buffer);
        }

        return $refactoredCondition;
    }

    /**
     * @param string $condition
     *
     * @throws ReflectionException
     *
     * @return string
     */
    private function refactorConditionPart(string $condition): string
    {
        $condition = str_replace('===', '==', $condition);
        $condition = str_replace('!==', '!=', $condition);
        $condition = str_replace('&&', 'and', $condition);
        $condition = str_replace('||', 'or', $condition);
        $condition = preg_replace('/!([^=])/', 'not $1', $condition);
        $condition = str_replace('.length', '|length', $condition);
        $condition = str_replace('.trim', '|trim', $condition);

//        $condition = $this->convertConcat($condition);

        foreach (Replacements::getConstants() as $constant => $value) {
            $condition = str_replace($value, Replacements::getSanitizedConstant($constant), $condition);
        }

        return $condition;
    }

    /**
     * @param string $content
     *
     * @throws ReflectionException
     *
     * @return string
     */
    public function refactorTextNode(string $content): string
    {
        $refactoredContent = '';
        $charsCount = mb_strlen($content, 'UTF-8');
        $open = false;
        $lastChar = null;
        $quoteChar = null;
        $buffer = '';

        for ($i = 0; $i < $charsCount; ++$i) {
            $char = mb_substr($content, $i, 1, 'UTF-8');
            if ($open === false) {
                $refactoredContent .= $char;
                if ($char === '{' && $lastChar === '{') {
                    $open = true;
                }
            } else {
                $buffer .= $char;
                if ($quoteChar === null && ($char === '"' || $char === '\'')) {
                    $quoteChar = $char;
                } elseif ($quoteChar === $char && $lastChar !== '\\') {
                    $quoteChar = null;
                }
                if ($quoteChar === null && $char === '}' && $lastChar === '}') {
                    $open = false;
                    $buffer = $this->convertTemplateString(trim($buffer, '}'));
                    $refactoredContent .= $this->refactorCondition($buffer) . '}}';
                    $buffer = '';
                }
            }
            $lastChar = $char;
        }

        return $refactoredContent;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function convertConcat(string $content): string
    {
        if (preg_match_all('/(\S*)(\s*\+\s*(\S+))+/', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $parts = explode('+', $match[0]);
                $lastPart = null;
                $convertedContent = '';
                foreach ($parts as $part) {
                    $part = trim($part);
                    if ($lastPart !== null) {
                        if (is_numeric($lastPart) && is_numeric($part)) {
                            $convertedContent .= ' + ' . $part;
                        } else {
                            $convertedContent .= ' ~ ' . $part;
                        }
                    } else {
                        $convertedContent = $part;
                    }
                    $lastPart = $part;
                }
                $content = str_replace($match[0], $convertedContent, $content);
            }
        }

        return $content;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    private function convertTemplateString(string $content): string
    {
        if (preg_match_all('/`([^`]+)`/', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $match[1] = str_replace('${', '\' ~ ', $match[1]);
                $match[1] = str_replace('}', ' ~ \'', $match[1]);
                $content = str_replace($match[0], '\'' . $match[1] . '\'', $content);
            }
        }

        return $content;
    }

    /**
     * @param string      $varName
     * @param string|null $fallbackVariableName
     *
     * @return string
     */
    public function createVariableOutput(string $varName, ?string $fallbackVariableName = null): string
    {
        if ($fallbackVariableName) {
            return '{{ ' . $varName . '|default(' . $fallbackVariableName . ') }}';
        }

        return '{{ ' . $varName . ' }}';
    }
}
