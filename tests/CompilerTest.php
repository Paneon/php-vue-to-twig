<?php

namespace Macavity\VueToTwig\Tests;

use DOMDocument;
use DOMNode;
use Macavity\VueToTwig\Compiler;

class CompilerTest extends TestCase
{
    /** @test */
    public function leavesMustacheVariablesIntact()
    {
        $compiler = new Compiler();

        $html = '<div>{{ someVariable }}</div>';
        $document = $this->createDocumentWithHtml($html);

        $result = $compiler->convertNode($document);
        $actual = $document->saveHTML($result);

        $this->assertEqualHtml($html, $actual);
    }

    private function createDocumentWithHtml(string $html): DOMDocument
    {
        $document = new DOMDocument();

        @$document->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return $document;
    }

    private function getTemplateNode(DOMDocument $document): DOMNode
    {
        return $document->getElementsByTagName('template')->item(0);
    }
}
