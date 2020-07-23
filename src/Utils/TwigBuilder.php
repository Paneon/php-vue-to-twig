<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Utils;

use DOMElement;
use Paneon\VueToTwig\Models\Property;
use Paneon\VueToTwig\Models\Replacements;
use Psr\Log\LoggerInterface;
use ReflectionException;
use RuntimeException;

class TwigBuilder
{
    protected const OPEN = 0;
    protected const CLOSE = 1;

    /**
     * @var mixed[]
     */
    protected $options;

    /**
     * @var LoggerInterface
     */
    private $logger;

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

    public function createSet(string $name): string
    {
        return $this->createBlock('set ' . $name);
    }

    public function closeSet(): string
    {
        return $this->createBlock('endset');
    }

    public function createVariable(string $name, string $assignment): string
    {
        return $this->createBlock('set ' . $name . ' = ' . $assignment);
    }

    public function createDefaultForVariable(string $name, string $defaultValue): string
    {
        return $this->createBlock('set ' . $name . ' = ' . $name . '|default(' . $defaultValue . ')');
    }

    public function createMultilineVariable(string $name, string $assignment): string
    {
        return $this->createBlock('set ' . $name)
            . $assignment
            . $this->createBlock('endset');
    }

    /**
     * @throws ReflectionException
     */
    public function createIf(string $condition): string
    {
        $condition = $this->refactorCondition($condition);

        return $this->createBlock('if ' . $condition);
    }

    /**
     * @throws ReflectionException
     */
    public function createElseIf(string $condition): string
    {
        $condition = $this->refactorCondition($condition);

        return $this->createBlock('elseif ' . $condition);
    }

    public function createElse(): string
    {
        return $this->createBlock('else');
    }

    public function createEndIf(): string
    {
        return $this->createBlock('endif');
    }

    public function createForItemInList(string $item, string $list): string
    {
        return $this->createBlock('for ' . $item . ' in ' . $list);
    }

    public function createForKeyInList(string $key, string $list): string
    {
        return $this->createBlock('for ' . $key . ' in ' . $list);
    }

    public function createFor(string $list, ?string $item = null, ?string $key = null): ?string
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

    public function createEndFor(): string
    {
        return $this->createBlock('endfor');
    }

    public function createComment(string $comment): string
    {
        return $this->options['tag_comment'][self::OPEN] . ' ' . $comment . ' ' . $this->options['tag_comment'][self::CLOSE];
    }

    /**
     * @param string[] $comments
     */
    public function createMultilineComment(array $comments): string
    {
        return $this->options['tag_comment'][self::OPEN] . ' ' . implode(
            "\n",
            $comments
        ) . ' ' . $this->options['tag_comment'][self::CLOSE];
    }

    public function createBlock(string $content): string
    {
        return "\n" . $this->options['tag_block'][self::OPEN] . ' ' . $content . ' ' . $this->options['tag_block'][self::CLOSE];
    }

    /**
     * @param Property[] $variables
     *
     * @throws ReflectionException
     */
    public function createIncludePartial(string $partialPath, array $variables = []): string
    {
        $classValues = [];
        foreach ($variables as $key => $variable) {
            if ($variable->getName() === 'class') {
                if ($variable->isBinding()) {
                    $classValues[] = str_replace(
                        ['{{', '}}', '__DOUBLE_CURLY_OPEN__', '__DOUBLE_CURLY_CLOSE__'],
                        ['(', ')', '', ''],
                        $this->handleBinding($variable->getValue(), $variable->getName())[0]
                    );
                } else {
                    $classValues[] = $variable->getValue();
                }
                unset($variables[$key]);
            }
        }

        $variables[] = new Property(
            'class',
            count($classValues) ? implode(' ~ " " ~ ', $classValues) : '""',
            false
        );

        $serializedProperties = $this->serializeComponentProperties($variables);

        return $this->createBlock('include "' . $partialPath . '" with ' . $serializedProperties);
    }

    /**
     * @param Property[] $properties
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

    public function sanitizeAttributeValue(string $value): string
    {
        $value = Replacements::sanitizeSingleReplacement($value, Replacements::PIPE);

        return $value;
    }

    /**
     * @throws ReflectionException
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
     * @throws ReflectionException
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
     * @throws ReflectionException
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

    public function createVariableOutput(string $varName, ?string $fallbackVariableName = null): string
    {
        if ($fallbackVariableName) {
            return '{{ ' . $varName . '|default(' . $fallbackVariableName . ') }}';
        }

        return '{{ ' . $varName . ' }}';
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws ReflectionException
     */
    public function handleBinding(string $value, string $name, ?DOMElement $node = null)
    {
        $dynamicValues = [];

        $regexArrayBinding = '/^\[([^\]]+)\]$/';
        $regexArrayElements = '/((?:[\'"])(?<elements>[^\'"])[\'"])/';
        $regexTemplateString = '/^`(?P<content>.+)`$/';
        $regexObjectBinding = '/^\{(?<elements>[^\}]+)\}$/';
        $regexObjectElements = '/["\']?(?<class>[^"\']+)["\']?:\s*(?<condition>[^,]+)/x';

        if ($value === 'true') {
            $this->logger->debug('- setAttribute ' . $name);
            if ($node) {
                $node->setAttribute($name, $name);
            }
        } elseif (preg_match($regexArrayBinding, $value, $matches)) {
            $this->logger->debug('- array binding ', ['value' => $value]);

            if (preg_match_all($regexArrayElements, $value, $arrayMatch)) {
                $value = $arrayMatch['elements'];
                $this->logger->debug('- ', ['match' => $arrayMatch]);
            } else {
                $value = [];
            }

            if ($name === 'style') {
                foreach ($value as $prop => $setting) {
                    if ($setting) {
                        $prop = strtolower($this->transformCamelCaseToCSS($prop));
                        $dynamicValues[] = sprintf('%s:%s', $prop, $setting);
                    }
                }
            } elseif ($name === 'class') {
                foreach ($value as $className) {
                    $dynamicValues[] = $className;
                }
            }
        } elseif (preg_match($regexObjectBinding, $value, $matches)) {
            $this->logger->debug('- object binding ', ['value' => $value]);

            $items = explode(',', $matches['elements']);

            foreach ($items as $item) {
                if (preg_match($regexObjectElements, $item, $matchElement)) {
                    $dynamicValues[] = sprintf(
                        '{{ %s ? \'%s\' }}',
                        $this->refactorCondition($matchElement['condition']),
                        $matchElement['class'] . ' '
                    );
                }
            }
        } elseif (preg_match($regexTemplateString, $value, $matches)) {
            // <div :class="`abc ${someDynamicClass}`">
            $templateStringContent = $matches['content'];

            preg_match_all('/\${([^}]+)}/', $templateStringContent, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $templateStringContent = str_replace(
                    $match[0],
                    '{{ ' . $this->refactorCondition($match[1]) . ' }}',
                    $templateStringContent
                );
            }

            $dynamicValues[] = $templateStringContent;
        } else {
            $value = $this->refactorCondition($value);
            $this->logger->debug(sprintf('- setAttribute "%s" with value "%s"', $name, $value));
            $dynamicValues[] =
                Replacements::getSanitizedConstant('DOUBLE_CURLY_OPEN') .
                $value .
                Replacements::getSanitizedConstant('DOUBLE_CURLY_CLOSE');
        }

        return $dynamicValues;
    }

    /**
     * @throws RuntimeException
     */
    public function transformCamelCaseToCSS(string $property): string
    {
        $cssProperty = preg_replace('/([A-Z])/', '-$1', $property);

        if (!$cssProperty) {
            throw new RuntimeException(sprintf('Failed to convert style property %s into css property name.', $property));
        }

        return $cssProperty;
    }
}
