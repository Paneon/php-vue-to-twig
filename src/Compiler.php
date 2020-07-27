<?php

declare(strict_types=1);

namespace Paneon\VueToTwig;

use DOMAttr;
use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Exception;
use Paneon\VueToTwig\Models\Component;
use Paneon\VueToTwig\Models\Property;
use Paneon\VueToTwig\Models\Replacements;
use Paneon\VueToTwig\Models\Slot;
use Paneon\VueToTwig\Utils\TwigBuilder;
use Psr\Log\LoggerInterface;
use ReflectionException;
use RuntimeException;

class Compiler
{
    protected const INCLUDE_BINDING = 'include';

    /**
     * @var Component[]
     */
    protected $components;

    /**
     * @var DOMDocument
     */
    protected $document;

    /**
     * @var DOMText[]|null
     */
    protected $lastCloseIf;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string[]
     */
    protected $banner;

    /**
     * @var TwigBuilder
     */
    protected $builder;

    /**
     * @var Property[]
     */
    protected $properties;

    /**
     * @var mixed[]
     */
    protected $replaceVariables = [];

    /**
     * @var mixed[]
     */
    protected $variables = [];

    /**
     * @var bool
     */
    protected $stripWhitespace = true;

    /**
     * @var string[]
     */
    protected $rawBlocks = [];

    /**
     * Compiler constructor.
     */
    public function __construct(DOMDocument $document, LoggerInterface $logger)
    {
        $this->builder = new TwigBuilder();
        $this->document = $document;
        $this->logger = $logger;
        $this->lastCloseIf = [];
        $this->components = [];
        $this->banner = [];
        $this->properties = [];
        $this->rawBlocks = [];

        $this->logger->debug("\n--------- New Compiler Instance ----------\n");
    }

    /**
     * @param string|string[] $strings
     */
    public function setBanner($strings): void
    {
        if (!is_array($strings)) {
            $strings = [$strings];
        }

        $this->banner = $strings;
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function convert(): string
    {
        /** @var DOMElement|null $templateElement */
        $templateElement = $this->document->getElementsByTagName('template')->item(0);

        /** @var DOMElement|null $scriptElement */
        $scriptElement = $this->document->getElementsByTagName('script')->item(0);

        $twigBlocks = $this->document->getElementsByTagName('twig');

        if ($scriptElement) {
            $this->registerProperties($scriptElement);
            $this->insertDefaultValues();
        }

        if ($twigBlocks->length) {
            foreach ($twigBlocks as $twigBlock) {
                /* @var DOMText $twigBlock */
                $this->rawBlocks[] = trim($twigBlock->textContent);
            }
        }

        if (!$templateElement) {
            throw new Exception('The template file does not contain a template tag.');
        }

        $resultNode = $this->convertNode($templateElement);
        $html = $this->document->saveHTML($resultNode);

        if (count($this->rawBlocks)) {
            $html = implode("\n", $this->rawBlocks) . "\n" . $html;
        }

        if (!$html) {
            throw new Exception('Generating html during conversion process failed.');
        }

        $html = $this->addVariableBlocks($html);
        $html = $this->replacePlaceholders($html);

        $html = preg_replace('/<template>\s*(.*)\s*<\/template>/ism', '$1', $html);
        $html = preg_replace('/<\/?template[^>]*?>/i', '', $html);

        if ($this->stripWhitespace) {
            $html = $this->stripWhitespace($html);
        }

        if (!empty($this->banner)) {
            $html = $this->addBanner($html);
        }

        return $html;
    }

    /**
     * @throws Exception
     */
    public function convertNode(DOMNode $node, int $level = 0): DOMNode
    {
        if ($node instanceof DOMComment) {
            $this->handleCommentNode($node);

            return $node;
        } elseif ($node instanceof DOMText) {
            return $this->handleTextNode($node);
        } elseif ($node instanceof DOMDocument) {
            $this->logger->warning('Document node found.');
        } elseif ($node instanceof DOMElement) {
            $this->replaceShowWithIf($node);
            $this->handleIf($node, $level);
            $this->handleFor($node);
            $this->handleHtml($node);
            $this->handleText($node);
            $this->stripEventHandlers($node);
            $this->handleDefaultSlot($node);
            $this->cleanupAttributes($node);
        }

        // Registered Component
        if (in_array($node->nodeName, array_keys($this->components))) {
            $matchedComponent = $this->components[$node->nodeName];
            $usedComponent = new Component($matchedComponent->getName(), $matchedComponent->getPath());

            if ($node->hasAttributes()) {
                /** @var DOMAttr $attribute */
                foreach ($node->attributes as $attribute) {
                    if (strpos($attribute->name, 'v-bind:') === 0 || strpos($attribute->name, ':') === 0) {
                        $name = substr($attribute->name, strpos($attribute->name, ':') + 1);
                        $value = $attribute->value;

                        if (substr_count($value, '`')) {
                            $value = $this->refactorTemplateString($attribute->value);
                        } else {
                            $value = $this->builder->refactorCondition($value);
                        }

                        $usedComponent->addProperty($name, $value, true);
                    } else {
                        $usedComponent->addProperty($attribute->name, '"' . $attribute->value . '"', false);
                    }
                }
            }

            foreach (iterator_to_array($node->childNodes) as $childNode) {
                $this->convertNode($childNode, $level + 1);
            }

            // Slots (Default)
            if ($node->hasChildNodes()) {
                $innerHtml = $this->innerHtmlOfNode($node);
                $innerHtml = $this->replacePlaceholders($innerHtml);
                $this->logger->debug(
                    'Add default slot:',
                    [
                        'nodeValue' => $node->nodeValue,
                        'innerHtml' => $innerHtml,
                    ]
                );

                $slot = $usedComponent->addDefaultSlot($innerHtml);

                $this->addReplaceVariable($slot->getSlotContentVariableString(), $slot->getValue());
            }

            // Include Partial
            $include = $this->document->createTextNode(
                $this->builder->createIncludePartial(
                    $usedComponent->getPath(),
                    $this->preparePropertiesForInclude($usedComponent->getProperties())
                )
            );

            $node->parentNode->insertBefore($include, $node);

            if ($usedComponent->hasSlots()) {
                foreach ($usedComponent->getSlots() as $slotName => $slot) {
                    // Add variable which contains the content (set)
                    $openSet = $this->document->createTextNode(
                        $this->builder->createSet($slot->getSlotValueName())
                    );
                    $node->parentNode->insertBefore($openSet, $include);

                    $setContent = $this->document->createTextNode($slot->getSlotContentVariableString());

                    $node->parentNode->insertBefore($setContent, $include);

                    // Close variable (endset)
                    $closeSet = $this->document->createTextNode(
                        $this->builder->closeSet()
                    );
                    $node->parentNode->insertBefore($closeSet, $include);
                }
            }

            // Remove original node
            $node->parentNode->removeChild($node);

            return $node;
        }

        if ($node instanceof DOMElement) {
            $this->handleAttributeBinding($node);
            if ($level === 1) {
                $this->handleRootNodeAttribute($node, 'class');
            }
        }

        foreach (iterator_to_array($node->childNodes) as $childNode) {
            $this->convertNode($childNode, $level + 1);
        }

        return $node;
    }

    /**
     * @param Property[] $variables
     *
     * @throws ReflectionException
     *
     * @return Property[]
     */
    private function preparePropertiesForInclude(array $variables): array
    {
        $values = [];
        foreach ($variables as $key => $variable) {
            if ($variable->getName() === 'class') {
                if ($variable->isBinding()) {
                    $values[$variable->getName()][] = $this->handleBinding(
                        $variable->getValue(),
                        $variable->getName(),
                        null,
                        false
                    )[0];
                } else {
                    $values[$variable->getName()][] = $variable->getValue();
                }
                unset($variables[$key]);
            }
        }

        $variables[] = new Property(
            'class',
            $values['class'] ?? null ? implode(' ~ " " ~ ', $values['class']) : '""',
            false
        );

        return $variables;
    }

    public function registerProperties(DOMElement $scriptElement): void
    {
        $content = $this->innerHtmlOfNode($scriptElement);

        $regexProps = '/(?<prop>[^\s\:]+)\:\s*\{(?<definition>[^\{\}]+)\}/mx';

        if (preg_match_all($regexProps, $content, $matches)) {
            foreach ($matches['prop'] as $i => $propName) {
                if (in_array($propName, ['props', 'methods', 'computed'])) {
                    continue;
                }

                $definition = $matches['definition'][$i];
                $property = new Property($propName, '', true);

                if (preg_match('/required:\s*true/m', $definition)) {
                    $property->setIsRequired(true);
                }

                if (preg_match('/default:\s*(?<default>[^,$]+)\s*,?/mx', $definition, $matchDefault)) {
                    $property->setDefault(trim($matchDefault['default']));
                }

                $this->properties[$propName] = $property;
            }
        }

        $typeScriptRegexProps = '/\@Prop\(.*?default\s*\:\s*(?<defaultValue>\'(?:[^\n](?!(?<![\\\\])\'))*.?\'|"(?:[^\n](?!(?<![\\\\])"))*.?"|[a-zA-Z0-9_]+).*?\)[^;]*?(?<propName>[a-zA-Z0-9_$]+)\!?\:[^;\@]*;/msx';

        if (preg_match_all($typeScriptRegexProps, $content, $typeScriptMatches, PREG_SET_ORDER)) {
            $this->properties = [];
            foreach ($typeScriptMatches as $typeScriptMatch) {
                $property = new Property($typeScriptMatch['propName'], '', true);
                $property->setDefault(trim($typeScriptMatch['defaultValue']));
                $this->properties[$typeScriptMatch['propName']] = $property;
            }
        }
    }

    public function replaceShowWithIf(DOMElement $node): void
    {
        if ($node->hasAttribute('v-show')) {
            $node->setAttribute('v-if', $node->getAttribute('v-show'));
            $node->removeAttribute('v-show');
        }
    }

    /**
     * @throws ReflectionException
     */
    private function handleAttributeBinding(DOMElement $node): void
    {
        /** @var DOMAttr $attribute */
        foreach (iterator_to_array($node->attributes) as $attribute) {
            if (strpos($attribute->name, 'v-bind:') !== 0 && strpos($attribute->name, ':') !== 0) {
                $this->logger->debug('- skip: ' . $attribute->name);

                continue;
            }

            $name = substr($attribute->name, strpos($attribute->name, ':') + 1);
            $value = $this->builder->sanitizeAttributeValue($attribute->value);
            $this->logger->debug('- handle: ' . $name . ' = ' . $value);

            $staticValues = $node->hasAttribute($name) ? $node->getAttribute($name) : '';

            // Remove originally bound attribute
            $this->logger->debug('- remove original ' . $attribute->name);
            $node->removeAttribute($attribute->name);

            if ($name === 'key') {
                continue;
            }

            $dynamicValues = $this->handleBinding($value, $name, $node);

            /* @see https://gitlab.gnome.org/GNOME/libxml2/-/blob/LIBXML2.6.32/HTMLtree.c#L657 */
            switch ($name) {
                case 'href':
                    $name = Replacements::getSanitizedConstant('ATTRIBUTE_NAME_HREF');
                    break;
                case 'action':
                    $name = Replacements::getSanitizedConstant('ATTRIBUTE_NAME_ACTION');
                    break;
                case 'src':
                    $name = Replacements::getSanitizedConstant('ATTRIBUTE_NAME_SRC');
                    break;
                case 'name':
                    if ($node->tagName === 'a') {
                        $name = Replacements::getSanitizedConstant('ATTRIBUTE_NAME_A_NAME');
                    }
                    break;
                default:
                    break;
            }

            $node->setAttribute($name, $this->implodeAttributeValue($name, $dynamicValues, $staticValues));
        }
    }

    /**
     * @throws ReflectionException
     *
     * @return string[]
     */
    public function handleBinding(string $value, string $name, ?DOMElement $node = null, bool $twigOutput = true): array
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
                    $dynamicValues[] = $this->prepareBindingOutput(
                        $this->builder->refactorCondition($matchElement['condition']) . ' ? \'' . $matchElement['class'] . ' \'',
                        $twigOutput
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
                    $this->prepareBindingOutput($this->builder->refactorCondition($match[1]), $twigOutput),
                    $templateStringContent
                );
            }

            $dynamicValues[] = $templateStringContent;
        } else {
            $value = $this->builder->refactorCondition($value);
            $this->logger->debug(sprintf('- setAttribute "%s" with value "%s"', $name, $value));
            $dynamicValues[] = $this->prepareBindingOutput($value, $twigOutput);
        }

        return $dynamicValues;
    }

    private function prepareBindingOutput(string $value, bool $twigOutput = true): string
    {
        $open = Replacements::getSanitizedConstant('DOUBLE_CURLY_OPEN');
        $close = Replacements::getSanitizedConstant('DOUBLE_CURLY_CLOSE');

        if (!$twigOutput) {
            $open = '(';
            $close = ')';
        }

        return $open . ' ' . $value . ' ' . $close;
    }

    /**
     * @throws ReflectionException
     */
    protected function handleTextNode(DOMText $node): DOMText
    {
        if (!empty(trim($node->textContent))) {
            $node->textContent = $this->builder->refactorTextNode($node->textContent);
        }

        return $node;
    }

    private function cleanupAttributes(DOMElement $node): void
    {
        $removeAttributes = [];
        /** @var DOMAttr $attribute */
        foreach ($node->attributes as $attribute) {
            if (
                (preg_match('/^v-([a-z]*)/', $attribute->name, $matches) === 1 && $matches[1] !== 'bind')
                || preg_match('/^[:]?ref$/', $attribute->name) === 1
            ) {
                $removeAttributes[] = $attribute->name;
            }
        }
        foreach ($removeAttributes as $removeAttribute) {
            $node->removeAttribute($removeAttribute);
        }
    }

    /**
     * @throws ReflectionException
     */
    private function handleIf(DOMElement $node, int $level): void
    {
        if (!$node->hasAttribute('v-if') &&
            !$node->hasAttribute('v-else-if') &&
            !$node->hasAttribute('v-else')) {
            return;
        }

        if ($node->hasAttribute('v-if')) {
            if ($node->hasAttribute('data-twig-if')) {
                $condition = $node->getAttribute('data-twig-if');
            } else {
                $condition = $node->getAttribute('v-if');
            }

            // Open with if
            $openIf = $this->document->createTextNode($this->builder->createIf($condition));
            $node->parentNode->insertBefore($openIf, $node);

            // Close with endif
            $closeIf = $this->document->createTextNode($this->builder->createEndIf());
            $node->parentNode->insertBefore($closeIf, $node->nextSibling);

            $this->lastCloseIf[$level] = $closeIf;

            $node->removeAttribute('v-if');
            $node->removeAttribute('data-twig-if');
        } elseif ($node->hasAttribute('v-else-if')) {
            if ($node->hasAttribute('data-twig-if')) {
                $condition = $node->getAttribute('data-twig-if');
            } else {
                $condition = $node->getAttribute('v-else-if');
            }

            // Replace old endif with else
            $this->lastCloseIf[$level]->textContent = $this->builder->createElseIf($condition);

            // Close with new endif
            $closeIf = $this->document->createTextNode($this->builder->createEndIf());
            $node->parentNode->insertBefore($closeIf, $node->nextSibling);
            $this->lastCloseIf[$level] = $closeIf;

            $node->removeAttribute('v-else-if');
            $node->removeAttribute('data-twig-if');
        } elseif ($node->hasAttribute('v-else')) {
            // Replace old endif with else
            $this->lastCloseIf[$level]->textContent = $this->builder->createElse();

            // Close with new endif
            $closeIf = $this->document->createTextNode($this->builder->createEndIf());
            $node->parentNode->insertBefore($closeIf, $node->nextSibling);
            $this->lastCloseIf[$level] = $closeIf;

            $node->removeAttribute('v-else');
        }
    }

    private function handleFor(DOMElement $node): void
    {
        /** @var DOMElement $node */
        if (!$node->hasAttribute('v-for')) {
            return;
        }

        [$forLeft, $listName] = explode(' in ', $node->getAttribute('v-for'));

        /*
         * Variations:
         * (1) item in array
         * (2)
         * (3) key, item in array
         * (4) key, item, index in object
         */

        // (2)
        if (preg_match('/(\d+)/', $listName)) {
            $listName = '1..' . $listName;
        }

        // (1)
        $forCommand = $this->builder->createForItemInList($forLeft, $listName);

        if (strpos($forLeft, ',')) {
            $forLeft = str_replace('(', '', $forLeft);
            $forLeft = str_replace(')', '', $forLeft);

            $forLeftArray = explode(',', $forLeft);

            $forValue = $forLeftArray[0];
            $forKey = $forLeftArray[1];
            $forIndex = $forLeftArray[2] ?? null;

            // (3)
            $forCommand = $this->builder->createFor($listName, $forValue, $forKey);

            if ($forIndex) {
                // (4)
                $forCommand .= $this->builder->createVariable((string) $forIndex, 'loop.index0');
            }
        }

        $startFor = $this->document->createTextNode($forCommand);
        $node->parentNode->insertBefore($startFor, $node);

        // End For
        $endFor = $this->document->createTextNode($this->builder->createEndFor());
        $node->parentNode->insertBefore($endFor, $node->nextSibling);

        $node->removeAttribute('v-for');
    }

    private function handleHtml(DOMElement $node): void
    {
        if (!$node->hasAttribute('v-html')) {
            return;
        }

        $html = $node->getAttribute('v-html');
        $node->removeAttribute('v-html');
        while ($node->hasChildNodes()) {
            $node->removeChild($node->firstChild);
        }
        $node->appendChild(new DOMText('{{' . $html . '|raw}}'));
    }

    private function handleText(DOMElement $node): void
    {
        if (!$node->hasAttribute('v-text')) {
            return;
        }

        $text = $node->getAttribute('v-text');
        $node->removeAttribute('v-text');
        while ($node->hasChildNodes()) {
            $node->removeChild($node->firstChild);
        }
        $node->appendChild(new DOMText('{{' . $text . '}}'));
    }

    protected function addDefaultsToVariable(string $varName, string $string): string
    {
        if (!in_array($varName, array_keys($this->properties))) {
            return $string;
        }

        $prop = $this->properties[$varName];

        if ($prop->hasDefault()) {
            $string = preg_replace(
                '/\b(' . $varName . ')\b/',
                $varName . '|default(' . $prop->getDefault() . ')',
                $string
            );
        }

        return $string;
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

    private function stripEventHandlers(DOMElement $node): void
    {
        $removeAttributes = [];
        /** @var DOMAttr $attribute */
        foreach ($node->attributes as $attribute) {
            if (strpos($attribute->name, 'v-on:') === 0 || strpos($attribute->name, '@') === 0) {
                $removeAttributes[] = $attribute->name;
            }
        }
        foreach ($removeAttributes as $removeAttribute) {
            $node->removeAttribute($removeAttribute);
        }
    }

    /**
     * @param mixed[] $values
     */
    protected function implodeAttributeValue(string $attribute, array $values, string $oldValue): string
    {
        $glue = ' ';

        if ($attribute === 'style') {
            $glue = '; ';
        }

        if (!empty($oldValue)) {
            $values = array_merge([$oldValue], $values);
        }

        return implode($glue, $values);
    }

    /**
     * @throws ReflectionException
     */
    protected function replacePlaceholders(string $string): string
    {
        foreach (Replacements::getConstants() as $constant => $value) {
            $string = str_replace(Replacements::getSanitizedConstant($constant), $value, $string);
        }

        foreach ($this->replaceVariables as $safeString => $value) {
            $string = str_replace($safeString, $value, $string);
        }

        return $string;
    }

    public function registerComponent(string $componentName, string $componentPath): void
    {
        $this->components[strtolower($componentName)] = new Component($componentName, $componentPath);
    }

    protected function addSingleLineBanner(string $html): string
    {
        return $this->builder->createComment(implode('', $this->banner)) . "\n" . $html;
    }

    protected function addBanner(string $html): string
    {
        if (count($this->banner) === 1) {
            return $this->addSingleLineBanner($html);
        }

        $bannerLines = ['{#'];

        foreach ($this->banner as $line) {
            $bannerLines[] = ' # ' . $line;
        }

        $bannerLines[] = ' #}';

        $html = implode(PHP_EOL, $bannerLines) . PHP_EOL . $html;

        return $html;
    }

    public function refactorTemplateString(string $value): string
    {
        if (preg_match('/^`(?P<content>.+)`$/', $value, $matches)) {
            $templateStringContent = '"' . $matches['content'] . '"';
            $value = preg_replace(
                '/\${(.+)}/',
                '{{ $1 }}',
                $templateStringContent
            );
        }

        return $value;
    }

    public function innerHtmlOfNode(DOMNode $element): string
    {
        $innerHTML = '';
        $children = $element->childNodes;

        foreach ($children as $child) {
            /** @var DOMNode $child */
            $html = $element->ownerDocument->saveHTML($child);

            if (!$html) {
                throw new RuntimeException(sprintf('Generation of html for child element %s failed', $child->nodeName));
            }

            $innerHTML .= trim($html);
        }

        return $innerHTML;
    }

    public function stripWhitespace(string $html): string
    {
        $html = preg_replace('/(\s)+/s', '\\1', $html);
        $html = str_replace("\n", '', $html);

        // Trim node text
        $html = preg_replace('/>[^\S ]+/s', '>', $html);
        $html = preg_replace('/[^\S ]+</s', '<', $html);

        $html = preg_replace('/> </s', '><', $html);
        $html = preg_replace('/} </s', '}<', $html);
        $html = preg_replace('/> {/s', '>{', $html);
        $html = preg_replace('/} {/s', '}{', $html);

        return $html;
    }

    public function setStripWhitespace(bool $stripWhitespace): Compiler
    {
        $this->stripWhitespace = $stripWhitespace;

        return $this;
    }

    /**
     * @param mixed $value
     */
    protected function addReplaceVariable(string $safeString, $value): void
    {
        $this->replaceVariables[$safeString] = $value;
    }

    /**
     * @param mixed $value
     *
     * @throws Exception
     */
    protected function addVariable(string $name, $value): void
    {
        if (isset($this->variables[$name])) {
            throw new Exception("The variable $name is already registered.", 500);
        }

        $this->variables[$name] = $value;
    }

    protected function addVariableBlocks(string $string): string
    {
        $blocks = [];

        foreach ($this->variables as $varName => $varValue) {
            $blocks[] = $this->builder->createMultilineVariable($varName, $varValue);
        }

        return implode('', $blocks) . $string;
    }

    /**
     * @throws Exception
     */
    protected function handleDefaultSlot(DOMElement $node): void
    {
        if ($node->nodeName !== 'slot') {
            return;
        }

        $slotFallback = $node->hasChildNodes() ? $this->innerHtmlOfNode($node) : null;

        if ($slotFallback) {
            $this->addVariable('slot_default_fallback', $slotFallback);
            $variable = $this->builder->createVariableOutput(
                Slot::SLOT_PREFIX . Slot::SLOT_DEFAULT_NAME,
                'slot_default_fallback'
            );
        } else {
            $variable = $this->builder->createVariableOutput(Slot::SLOT_PREFIX . Slot::SLOT_DEFAULT_NAME);
        }

        $variableNode = $this->document->createTextNode($variable);

        $node->parentNode->insertBefore($variableNode, $node);
        $node->parentNode->removeChild($node);
    }

    protected function insertDefaultValues(): void
    {
        foreach ($this->properties as $property) {
            if (!$property->hasDefault()) {
                continue;
            }
            $this->rawBlocks[] = $this->builder->createDefaultForVariable(
                $property->getName(),
                $property->getDefault()
            );
        }
    }

    protected function handleRootNodeAttribute(DOMElement $node, ?string $name = null): DOMElement
    {
        if (!$name) {
            return $node;
        }
        $string = $this->prepareBindingOutput($name . '|default(\'\')');
        if ($node->hasAttribute($name)) {
            $attribute = $node->getAttributeNode($name);
            $attribute->value .= ' ' . $string;
        } else {
            $attribute = new DOMAttr($name, $string);
        }
        $node->setAttributeNode($attribute);

        return $node;
    }

    private function handleCommentNode(DOMComment $node): void
    {
        $nodeValue = trim($node->nodeValue);
        if (preg_match('/^(eslint-disable|@?todo)/i', $nodeValue) === 1) {
            $node->parentNode->removeChild($node);
        }
    }
}
