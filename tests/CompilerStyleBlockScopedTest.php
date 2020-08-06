<?php

namespace Paneon\VueToTwig\Tests;

use Exception;
use Paneon\VueToTwig\Utils\StyleBuilder;

class CompilerStyleBlockScopedTest extends AbstractTestCase
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
        $compiler->setStyleBlockOutputType(StyleBuilder::STYLE_ALL);
        $compiler->registerComponent('ChildComponent', '/templates/ChildComponent.twig');

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return $this->loadFixturesFromDir('style-block-scoped');
    }
}
