<?php

namespace Macavity\VueToTwig\Tests;

use Macavity\VueToTwig\Compiler;

class VueIfTest extends AbstractTestCase
{
    /**
     * @dataProvider ifProvider
     */
    public function testIf($html, $expected)
    {
        $document = $this->createDocumentWithHtml($html);
        $compiler = new Compiler($document);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function ifProvider()
    {
        return $this->loadFixturesFromDir('vue-if');
    }
}
