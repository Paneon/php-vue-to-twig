<?php

namespace Paneon\VueToTwig\Tests;

use Exception;
use Paneon\VueToTwig\Utils\StyleBuilder;

class CompilerStyleBlockScopedImportTest extends AbstractTestCase
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
        $compiler->setRelativePath(__DIR__ . '/fixtures/style-block-scoped-import/style-block-scss-scoped-import.vue');

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return $this->loadFixturesFromDir('style-block-scoped-import');
    }
}
