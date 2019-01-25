<?php declare(strict_types=1);

namespace Paneon\VueToTwig;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Exception;
use Paneon\VueToTwig\Models\Replacements;
use Psr\Log\LoggerInterface;

class Compiler
{

    /** @var String[] */
    protected $components;

    /** @var DOMDocument */
    protected $document;

    /** @var DOMText */
    protected $lastCloseIf;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string[] */
    protected $banner;

    public function __construct(DOMDocument $document, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->document = $document;
        $this->lastCloseIf = null;
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

        if (!empty($this->banner)) {
            $html = $this->addBanner($html);
        }

        return $html;
    }

    public function convertNode(DOMNode $node): DOMNode
    {
        switch($node->nodeType) {
            case XML_TEXT_NODE:
                $this->logger->debug('Text node found', ['name' => $node->nodeName]);
                // fall through to next case, because we don't need to handle either of these node-types
            case XML_COMMENT_NODE:
                $this->logger->debug('Comment node found', ['name' => $node->nodeName]);
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

        $this->stripEventHandlers($node);
        $this->handleFor($node);
        //$this->handleRawHtml($node, $data);

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
                $this->logger->debug("- skip: ".$attribute->name);
                continue;
            }

            $name = substr($attribute->name, strpos($attribute->name, ':') + 1);
            $value = $attribute->value;
            $this->logger->debug('- handle: '.$name.' = '.$value);


            switch ($name) {
                case 'key':
                    // Not necessary in twig
                    break;
                case 'style':
                    break;
                case 'class':
                    break;
                default:
                    if ($value === 'true') {
                        $this->logger->debug('- setAttribute '.$name);
                        $node->setAttribute($name, $name);
                    } else {
                        $this->logger->debug('- setAttribute "'.$name.'" with value');
                        $node->setAttribute(
                            $name,
                            Replacements::getSanitizedConstant('DOUBLE_CURLY_OPEN').
                            $value.
                            Replacements::getSanitizedConstant('DOUBLE_CURLY_CLOSE')
                        );
                    }
            }

            if (is_array($value)) {
                if ($name === 'style') {
                    $styles = [];
                    foreach ($value as $prop => $setting) {
                        if ($setting) {
                            $prop = strtolower(preg_replace('/([A-Z])/', '-$1', $prop));
                            $styles[] = sprintf('%s:%s', $prop, $setting);
                        }
                    }
                    $node->setAttribute($name, implode(';', $styles));
                } elseif ($name === 'class') {
                    $classes = [];
                    foreach ($value as $className => $setting) {
                        if ($setting) {
                            $classes[] = $className;
                        }
                    }
                    $node->setAttribute($name, implode(' ', $classes));
                }
            }

            $this->logger->debug('=> remove original '.$attribute->name);
            $node->removeAttribute($attribute->name);
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
            $condition = $node->getAttribute('v-if');
            $condition = $this->sanitizeCondition($condition);

            // Open with if
            $openIf = $this->document->createTextNode('{% if '.$condition.' %}');
            $node->parentNode->insertBefore($openIf, $node);

            // Close with endif
            $closeIf = $this->document->createTextNode('{% endif %}');
            $node->parentNode->insertBefore($closeIf, $node->nextSibling);

            $this->lastCloseIf = $closeIf;

            $node->removeAttribute('v-if');
        } elseif ($node->hasAttribute('v-else-if')) {
            $condition = $node->getAttribute('v-else-if');
            $condition = $this->sanitizeCondition($condition);

            // Replace old endif with else
            $this->lastCloseIf->textContent = '{% elseif '.$condition.' %}';

            // Close with new endif
            $closeIf = $this->document->createTextNode('{% endif %}');
            $node->parentNode->insertBefore($closeIf, $node->nextSibling);
            $this->lastCloseIf = $closeIf;

            $node->removeAttribute('v-else-if');
        } elseif ($node->hasAttribute('v-else')) {
            // Replace old endif with else
            $this->lastCloseIf->textContent = '{% else %}';

            // Close with new endif
            $closeIf = $this->document->createTextNode('{% endif %}');
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
            $listName = '1..'.$listName;
        }

        // (1)
        $forCommand = '{% for '.$forLeft.' in '.$listName.' %}';

        if (strpos($forLeft, ',')) {
            $forLeft = str_replace('(', '', $forLeft);
            $forLeft = str_replace(')', '', $forLeft);

            $forLeftArray = explode(',', $forLeft);

            $forValue = $forLeftArray[0];
            $forKey = $forLeftArray[1];
            $forIndex = $forLeftArray[2] ?? null;

            // (3)
            $forCommand = '{% for '.$forKey.', '.$forValue.' in '.$listName.' %}';

            if ($forIndex) {
                // (4)
                $forCommand .= ' {% set '.$forIndex.' = loop.index0 %}';
            }
        }

        $startFor = $this->document->createTextNode($forCommand);
        $node->parentNode->insertBefore($startFor, $node);

        // End For
        $endFor = $this->document->createTextNode('{% endfor %}');
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

        foreach ($nodes as $node) {
            if ($node->nodeType === XML_TEXT_NODE) {
                continue;
            } else {
                $tagNodes++;
                $firstTagNode = $node;
            }
        }

        if ($tagNodes > 1) {
            throw new Exception('Template should have only one root node');
        }

        return $firstTagNode;
    }

    protected function sanitizeCondition(string $condition)
    {
        $condition = str_replace('&&', 'and', $condition);
        $condition = str_replace('||', 'or', $condition);

        foreach (Replacements::getConstants() as $constant => $value) {
            $condition = str_replace($value, Replacements::getSanitizedConstant($constant), $condition);
        }

        return $condition;
    }

    protected function replacePlaceholders(string $string)
    {
        foreach (Replacements::getConstants() as $constant => $value) {
            $string = str_replace(Replacements::getSanitizedConstant($constant), $value, $string);
        }

        return $string;
    }

    protected function addSingleLineBanner(string $html)
    {
        return '{# '.implode('', $this->banner).' #}'."\n".$html;
    }

    protected function addBanner(string $html)
    {
        if (count($this->banner) === 1) {
            return $this->addSingleLineBanner($html);
        }

        $bannerLines = ['{#'];

        foreach ($this->banner as $line) {
            $bannerLines[] = ' # '.$line;
        }

        $bannerLines[] = ' #}';

        $html = implode("\n", $bannerLines)."\n".$html;

        return $html;
    }
}
