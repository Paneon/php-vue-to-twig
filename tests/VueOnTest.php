<?php

namespace Macavity\VueToTwig\Tests;

class VueOnTest extends AbstractTestCase
{
    /**
     * @dataProvider onProvider
     * @throws \Exception
     */
    public function testOn($html, $expected)
    {
        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function onProvider()
    {
        return $this->loadFixturesFromDir('vue-on');
    }
}
