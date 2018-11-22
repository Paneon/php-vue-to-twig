<?php declare(strict_types=1);

namespace Macavity\VueToTwig;

use DOMAttr;
use DOMCharacterData;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use Exception;
use LibXMLError;

class Compiler
{
    protected $data;

    /** @var String[] */
    protected $components;


    public function __construct()
    {
        $this->data = [];
    }

    /**
     * @throws Exception
     */
    public function convert()
    {
        $this->convertNode($this->rootElement);

        dd($this->document->saveHTML($this->rootElement));
    }

    public function convertNode(DOMNode $node)
    {
        if ($this->isTextNode($node)) {
            return;
        }
        return $node;
    }
    private function isRemovedFromTheDom(DOMNode $node)
    {
        return $node->parentNode === null;
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