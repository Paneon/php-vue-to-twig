<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class DataTest extends AbstractTestCase
{
    /**
     * @dataProvider dataProvider
     *
     * @param mixed $html
     * @param mixed $expected
     *
     * @throws Exception
     */
    public function testData($html, $expected)
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
        return $this->loadFixturesFromDir('data');
    }
}
