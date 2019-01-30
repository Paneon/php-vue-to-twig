<?php

namespace Paneon\VueToTwig\Tests;

class CompilerTest extends AbstractTestCase
{
    /** @test */
    public function leavesMustacheVariablesIntact()
    {
        $html = '<template><div>{{ someVariable }}</div></template>';
        $expected = '<div>{{ someVariable }}</div>';
        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /** @test */
    public function setBannerWithSingleLineAddsBannerCommentToTheTopOfTheTwigFile()
    {
        $html = '<template><div>{{ someVariable }}</div></template>';
        $expected = '{# This file was generated using VueToTwig #}<div>{{ someVariable }}</div>';
        $compiler = $this->createCompiler($html);
        $compiler->setBanner('This file was generated using VueToTwig');

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);

    }

    /** @test */
    public function setBannerAddsMultipleCommentsToTheTopOfTheTwigFile()
    {
        $html = '<template><div>{{ someVariable }}</div></template>';
        $expected = '{#
 # This file was generated using VueToTwig
 # Source: assets/js/SomeComponent.vue
 #}
<div>{{ someVariable }}</div>';

        $compiler = $this->createCompiler($html);
        $compiler->setBanner([
            'This file was generated using VueToTwig',
            'Source: assets/js/SomeComponent.vue',
        ]);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);

    }

    /** @test */
    public function canHandleUTF8()
    {
        $html = '<template><div>Äöü: 10,00€</div></template>';
        $expected = '<div>Äöü: 10,00€</div>';

        $compiler = $this->createCompiler($html);
        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
