<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class VueBindingsTest extends AbstractTestCase
{
    /**
     * @dataProvider dataProvider
     *
     * @param mixed $html
     * @param mixed $expected
     *
     * @throws Exception
     */
    public function testBindings($html, $expected)
    {
        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return $this->loadFixturesFromDir('vue-bind');
    }
}
