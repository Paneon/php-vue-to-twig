<?php

namespace Macavity\VueToTwig\Tests;

class VueIfTest extends AbstractTestCase
{
    /**
     * @dataProvider ifProvider
     */
    public function testIf($html, $expected)
    {
        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function ifProvider()
    {
        return $this->loadFixturesFromDir('vue-if');
    }
}
