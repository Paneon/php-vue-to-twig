<?php

namespace Macavity\VueToTwig\Tests;

use Macavity\VueToTwig\Compiler;

class VueForTest extends AbstractTestCase
{
    /**
     * @dataProvider dataProvider
     * @throws \Exception
     */
    public function testFor($html, $expected)
    {
        $document = $this->createDocumentWithHtml($html);
        $compiler = new Compiler($document);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function dataProvider()
    {
        return $this->loadFixturesFromDir('vue-for');
    }
}
