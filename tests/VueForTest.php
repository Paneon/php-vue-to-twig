<?php

namespace Macavity\VueToTwig\Tests;

class VueForTest extends AbstractTestCase
{
    /**
     * @dataProvider dataProvider
     * @throws \Exception
     */
    public function testFor($html, $expected)
    {
        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function dataProvider()
    {
        return $this->loadFixturesFromDir('vue-for');
    }
}
