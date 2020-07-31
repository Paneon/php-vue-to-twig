<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class CompilerStyleBlockTest extends AbstractTestCase
{
    /**
     * @dataProvider dataProvider
     *
     * @param mixed $html
     * @param mixed $expected
     *
     * @throws Exception
     */
    public function testStyleBlock($html, $expected)
    {
        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $actual = preg_replace('/data-v-[0-9a-z]{8}/', 'data-v-12345678', $actual);

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return $this->loadFixturesFromDir('style-block');
    }
}
