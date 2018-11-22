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
}
