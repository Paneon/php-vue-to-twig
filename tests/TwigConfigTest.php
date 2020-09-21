<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class TwigConfigTest extends AbstractTestCase
{
    /**
     * @dataProvider ifProvider
     *
     * @param mixed $html
     * @param mixed $expected
     *
     * @throws Exception
     */
    public function testTwigConfig($html, $expected)
    {
        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function ifProvider()
    {
        return $this->loadFixturesFromDir('twig-config');
    }
}
