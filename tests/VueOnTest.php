<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class VueOnTest extends AbstractTestCase
{
    /**
     * @dataProvider onProvider
     *
     * @param mixed $html
     * @param mixed $expected
     *
     * @throws Exception
     */
    public function testOn($html, $expected)
    {
        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @return array
     */
    public function onProvider()
    {
        return $this->loadFixturesFromDir('vue-on');
    }
}
