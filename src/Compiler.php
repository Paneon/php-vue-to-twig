<?php declare(strict_types=1);

namespace Macavity\VueToTwig;

use DOMAttr;
use DOMCharacterData;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Exception;
use LibXMLError;

class Compiler
{
    /** @var String[] */
    protected $components;

    /** @var DOMDocument */
    protected $document;

    /** @var DOMText */
    protected $lastCloseIf;

    public function __construct(DOMDocument $document)
    {
        $this->document = $document;

        $this->lastCloseIf = null;
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

        return $this->document->saveHTML($resultNode);
    }

    public function convertNode(DOMNode $node): DOMNode
    {
        if ($this->isTextNode($node)) {
            return $node;
        }

        if ($node->nodeType === XML_ELEMENT_NODE) {
            echo "\nElement node found";
            /** @var DOMElement $node */
            $this->replaceShowWithIf($node);
            $this->handleIf($node);
        }
        elseif($node->nodeType === XML_HTML_DOCUMENT_NODE) {
            echo "\nDocument node found.";
        }
//        else {
//            var_dump($node->nodeType);
//        }

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
                var_dump("- skip: ". $attribute->name);
                continue;
            }

            $name = substr($attribute->name, 1);
            $value = $attribute->value;
            var_dump('- handle: '.$name.' = '.$value);

            if (is_bool($value)) {
                if ($value) {
                    $node->setAttribute($name, $name);
                }
            } elseif (is_array($value)) {
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
            } else {
                $node->setAttribute($name, $value);
            }
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

            // Open with if
            $openIf = $this->document->createTextNode('{% if ' . $condition . ' %}');
            $node->parentNode->insertBefore($openIf, $node);

            // Close with endif
            $closeIf = $this->document->createTextNode('{% endif %}');
            $node->parentNode->insertBefore($closeIf, $node->nextSibling);

            $this->lastCloseIf = $closeIf;

            $node->removeAttribute('v-if');
        }
        elseif ($node->hasAttribute('v-else-if')) {
            $condition = $node->getAttribute('v-else-if');

            // Replace old endif with else
            $this->lastCloseIf->textContent = '{% elseif '.$condition.' %}';

            // Close with new endif
            $closeIf = $this->document->createTextNode('{% endif %}');
            $node->parentNode->insertBefore($closeIf, $node->nextSibling);
            $this->lastCloseIf = $closeIf;

            $node->removeAttribute('v-else-if');
        }
        elseif ($node->hasAttribute('v-else')) {
            echo "\nFound a v-else";

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
         * (2) key, item in array
         * (3) key, item, index in object
         */

        // (1)
        $forCommand = '{% for ' . $forLeft . ' in ' . $listName . ' %}';

        if (strpos($forLeft, ',')) {
            $forLeft = str_replace('(', '', $forLeft);
            $forLeft = str_replace(')', '', $forLeft);

            $forLeftArray = explode(',', $forLeft);

            $forValue = $forLeftArray[0];
            $forKey = $forLeftArray[1];
            $forIndex = $forLeftArray[2] ?? null;

            // (2)
            $forCommand = '{% for ' . $forKey . ', ' . $forValue . ' in ' . $listName . ' %}';

            if ($forIndex) {
                // (3)
                $forCommand .= ' {% set ' . $forIndex . ' = loop.index0 %}';
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

    private function isTextNode(DOMNode $node): bool
    {
        return $node instanceof DOMCharacterData;
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
}