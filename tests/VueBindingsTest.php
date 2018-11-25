<?php

namespace Macavity\VueToTwig\Tests;

class VueBindingsTest extends AbstractTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testBindings($html, $expected)
    {
        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function dataProvider()
    {
        return $this->loadFixturesFromDir('vue-bind');
    }
}
