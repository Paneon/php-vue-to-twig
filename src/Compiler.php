<?php declare(strict_types=1);

namespace Paneon\VueToTwig;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Exception;
use Paneon\VueToTwig\Models\Replacements;
use Paneon\VueToTwig\Utils\TwigBuilder;
use Psr\Log\LoggerInterface;

class Compiler
{

    /** @var Component[] */
    protected $components;

    /** @var DOMDocument */
    protected $document;

    /** @var DOMText */
    protected $lastCloseIf;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string[] */
    protected $banner;

    /**
     * @var TwigBuilder
     */
    protected $builder;

    protected $stripWhitespace = true;

    public function __construct(DOMDocument $document, LoggerInterface $logger)
    {
        $this->builder = new TwigBuilder();
        $this->document = $document;
        $this->logger = $logger;
        $this->lastCloseIf = null;
        $this->components = [];
        $this->banner = [];

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
     * @throws Exception
     */
    public function convert(): string
    {
        $templateElement = $this->document->getElementsByTagName('template')->item(0);

        if (!$templateElement) {
            throw new Exception('The template file does not contain a template tag.');
        }

        $rootNode = $this->getRootNode($templateElement);
        $resultNode = $this->convertNode($rootNode);
        $html = $this->document->saveHTML($resultNode);

        $html = $this->replacePlaceholders($html);

        if ($this->stripWhitespace) {
            $html = $this->stripWhitespace($html);
        }

        if (!empty($this->banner)) {
            $html = $this->addBanner($html);
        }

        return $html;
    }

    public function convertNode(DOMNode $node): DOMNode
    {
        switch ($node->nodeType) {
            // We don't need to handle either of these node-types
            case XML_TEXT_NODE:
            case XML_COMMENT_NODE:
                return $node;
            case XML_ELEMENT_NODE:
                /** @var DOMElement $node */
                $this->replaceShowWithIf($node);
                $this->handleIf($node);
                break;
            case XML_HTML_DOCUMENT_NODE:
                $this->logger->warning("Document node found.");
                break;
        }

        $this->handleFor($node);
        $this->stripEventHandlers($node);
        //$this->handleRawHtml($node, $data);

        if (in_array($node->nodeName, array_keys($this->components))) {
            $matchedComponent = $this->components[$node->nodeName];
            $usedComponent = new Component($matchedComponent->getName(), $matchedComponent->getPath());

            if ($node->hasAttributes()) {
                /** @var DOMAttr $attribute */
                foreach ($node->attributes as $attribute) {
                    if (strpos($attribute->name, 'v-bind:') === 0 || strpos($attribute->name, ':') === 0) {
                        $name = substr($attribute->name, strpos($attribute->name, ':') + 1);
                        $value = $this->refactorTemplateString($attribute->value);

                        $usedComponent->addProperty($name, $value, true);
                    } else {
                        $usedComponent->addProperty($attribute->name, '"' . $attribute->value . '"', false);
                    }
                }
            }

            $include = $this->document->createTextNode(
                $this->builder->createIncludePartial(
                    $usedComponent->getPath(),
                    $usedComponent->getProperties()
                )
            );

            $node->parentNode->insertBefore($include, $node);
            $node->parentNode->removeChild($node);

            return $node;
        }

        $this->handleAttributeBinding($node);

        foreach (iterator_to_array($node->childNodes) as $childNode) {
            $this->convertNode($childNode);
        }

        return $node;
    }

    public function replaceShowWithIf(DOMElement $node): void
    {
        if ($node->hasAttribute('v-show')) {
            $node->setAttribute('v-if', $node->getAttribute('v-show'));
            $node->removeAttribute('v-show');
        }
    }

    private function handleAttributeBinding(DOMElement $node)
    {
        /** @var DOMAttr $attribute */
        foreach (iterator_to_array($node->attributes) as $attribute) {

            if (strpos($attribute->name, 'v-bind:') !== 0 && strpos($attribute->name, ':') !== 0) {
                $this->logger->debug("- skip: " . $attribute->name);
                continue;
            }

            $name = substr($attribute->name, strpos($attribute->name, ':') + 1);
            $value = $attribute->value;
            $this->logger->debug('- handle: ' . $name . ' = ' . $value);

            $staticValues = $node->hasAttribute($name) ? $node->getAttribute($name) : '';
            $dynamicValues = [];

            // Remove originally bound attribute
            $this->logger->debug('- remove original ' . $attribute->name);
            $node->removeAttribute($attribute->name);

            switch ($name) {
                case 'key':
                    // Not necessary in twig
                    return;
                case 'style':
                    break;
                case 'class':
                    break;
            }

            $regexArrayBinding = '/^\[([^\]]+)\]$/';
            $regexArrayElements = '/((?:[\'"])(?<elements>[^\'"])[\'"])/';
            $regexTemplateString = '/^`(?P<content>.+)`$/';
            $regexObjectBinding = '/^\{(?<elements>[^\}]+)\}$/';
            $regexObjectElements = '/["\']?(?<class>[^"\']+)["\']?:\s*(?<condition>[^,]+)/x';

            if ($value === 'true') {
                $this->logger->debug('- setAttribute ' . $name);
                $node->setAttribute($name, $name);
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
                            $prop = strtolower(preg_replace('/([A-Z])/', '-$1', $prop));
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
                $this->logger->debug(print_r($items,true));

                foreach ($items as $item) {
                    if(preg_match($regexObjectElements, $item, $matchElement)){
                        $dynamicValues[] = sprintf(
                            '{{ %s ? \'%s\' }}',
                            $this->builder->refactorCondition($matchElement['condition']),
                            $matchElement['class']
                        );
                    }
                }

            } elseif (preg_match($regexTemplateString, $value, $matches)) {
                /*
                 * <div :class="`abc ${someDynamicClass}`">
                 */
                $templateStringContent = $matches['content'];

                $templateStringContent = preg_replace(
                    '/\$\{([^}]+)\}/',
                    '{{ $1 }}',
                    $templateStringContent
                );

                $dynamicValues[] = $templateStringContent;
            } else {
                $this->logger->debug('- setAttribute "' . $name . '" with value');
                $dynamicValues[] =
                    Replacements::getSanitizedConstant('DOUBLE_CURLY_OPEN') .
                    $value .
                    Replacements::getSanitizedConstant('DOUBLE_CURLY_CLOSE');
            }

            $node->setAttribute(
                $name,
                $this->implodeAttributeValue($name, $dynamicValues, $staticValues)
            );
        }
    }

    private function handleIf(DOMElement $node): void
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

            $this->lastCloseIf = $closeIf;

            $node->removeAttribute('v-if');
            $node->removeAttribute('data-twig-if');
        } elseif ($node->hasAttribute('v-else-if')) {

            if ($node->hasAttribute('data-twig-if')) {
                $condition = $node->getAttribute('data-twig-if');
            } else {
                $condition = $node->getAttribute('v-else-if');
            }

            // Replace old endif with else
            $this->lastCloseIf->textContent = $this->builder->createElseIf($condition);

            // Close with new endif
            $closeIf = $this->document->createTextNode($this->builder->createEndIf());
            $node->parentNode->insertBefore($closeIf, $node->nextSibling);
            $this->lastCloseIf = $closeIf;

            $node->removeAttribute('v-else-if');
            $node->removeAttribute('data-twig-if');
        } elseif ($node->hasAttribute('v-else')) {
            // Replace old endif with else
            $this->lastCloseIf->textContent = $this->builder->createElse();

            // Close with new endif
            $closeIf = $this->document->createTextNode($this->builder->createEndIf());
            $node->parentNode->insertBefore($closeIf, $node->nextSibling);
            $this->lastCloseIf = $closeIf;

            $node->removeAttribute('v-else');
        }
    }

    private function handleFor(DOMElement $node)
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
                $forCommand .= $this->builder->createVariable($forIndex, 'loop.index0');
            }
        }

        $startFor = $this->document->createTextNode($forCommand);
        $node->parentNode->insertBefore($startFor, $node);

        // End For
        $endFor = $this->document->createTextNode($this->builder->createEndFor());
        $node->parentNode->insertBefore($endFor, $node->nextSibling);

        $node->removeAttribute('v-for');
    }

    private function stripEventHandlers(DOMElement $node)
    {
        /** @var DOMAttr $attribute */
        foreach ($node->attributes as $attribute) {
            if (strpos($attribute->name, 'v-on:') === 0 || strpos($attribute->name, '@') === 0) {
                $node->removeAttribute($attribute->name);
            }
        }
    }

    /**
     * @throws Exception
     */
    private function getRootNode(DOMElement $element): \DOMNode
    {
        /** @type \DOMNode[] */
        $nodes = iterator_to_array($element->childNodes);

        $tagNodes = 0;
        $firstTagNode = null;

        /** @var DOMNode $node */
        foreach ($nodes as $node) {
            if ($node->nodeType === XML_TEXT_NODE) {
                continue;
            } elseif (in_array($node->nodeName, ['script', 'style'])) {
                continue;
            } else {
                $tagNodes++;
                $firstTagNode = $node;
            }
        }

        if ($tagNodes > 1) {
            //throw new Exception('Template should have only one root node');
        }

        return $firstTagNode;
    }

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

    protected function replacePlaceholders(string $string)
    {
        foreach (Replacements::getConstants() as $constant => $value) {
            $string = str_replace(Replacements::getSanitizedConstant($constant), $value, $string);
        }

        return $string;
    }

    public function registerComponent(string $componentName, string $componentPath)
    {
        $this->components[strtolower($componentName)] = new Component($componentName, $componentPath);
    }

    protected function addSingleLineBanner(string $html)
    {
        return $this->builder->createComment(implode('', $this->banner)) . "\n" . $html;
    }

    protected function addBanner(string $html)
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

    public function refactorTemplateString($value)
    {
        if (preg_match('/^`(?P<content>.+)`$/', $value, $matches)) {
            $templateStringContent = '"' . $matches['content'] . '"';
            $value = preg_replace(
                '/\$\{(.+)\}/',
                '{{ $1 }}',
                $templateStringContent
            );
        }

        return $value;
    }

    public function stripWhitespace($html)
    {
        $html = preg_replace('/(\s)+/s', '\\1', $html);
        $html = str_replace("\n", '', $html);

        // Trim node text
        $html = preg_replace('/\>[^\S ]+/s', ">", $html);
        $html = preg_replace('/[^\S ]+\</s', "<", $html);

        $html = preg_replace('/> </s', '><', $html);
        $html = preg_replace('/} </s', '}<', $html);
        $html = preg_replace('/> {/s', '>{', $html);
        $html = preg_replace('/} {/s', '}{', $html);

        return $html;
    }

    /**
     * @param bool $stripWhitespace
     *
     * @return Compiler
     */
    public function setStripWhitespace(bool $stripWhitespace): Compiler
    {
        $this->stripWhitespace = $stripWhitespace;

        return $this;
    }
}
