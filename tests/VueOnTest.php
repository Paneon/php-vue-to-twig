<?php

namespace Macavity\VueToTwig\Tests;

use Macavity\VueToTwig\Compiler;

class VueOnTest extends AbstractTestCase
{
    /**
     * @dataProvider onProvider
     * @throws \Exception
     */
    public function testOn($html, $expected)
    {
        $document = $this->createDocumentWithHtml($html);
        $compiler = new Compiler($document);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function onProvider()
    {
        return $this->loadFixturesFromDir('vue-on');
    }
}
