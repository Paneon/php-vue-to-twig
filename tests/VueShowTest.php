<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class VueShowTest extends AbstractTestCase
{
    /**
     * @dataProvider showProvider
     *
     * @param mixed $html
     * @param mixed $expected
     *
     * @throws Exception
     */
    public function testIf($html, $expected)
    {
        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function showProvider()
    {
        return $this->loadFixturesFromDir('vue-show');
    }
}
