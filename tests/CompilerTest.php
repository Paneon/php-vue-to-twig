<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class CompilerTest extends AbstractTestCase
{
    /**
     * @test
     *
     * @throws Exception
     */
    public function leavesMustacheVariablesIntact()
    {
        $html = '<template><div>{{ someVariable }}</div></template>';
        $expected = '<div class="{{class|default(\'\')}}">{{ someVariable }}</div>';
        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function stripsOutWhitespaceBetweenTags()
    {
        $html = '
<template>
  
  <div>
                    {{ someVariable }}
  </div>
  
</template>';

        $expected = '<div class="{{class|default(\'\')}}">{{ someVariable }}</div>';

        $compiler = $this->createCompiler($html);
        $actual = $compiler->convert();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function setBannerWithSingleLineAddsBannerCommentToTheTopOfTheTwigFile()
    {
        $html = '<template><div>{{ someVariable }}</div></template>';
        $expected = '{# This file was generated using VueToTwig #}<div class="{{class|default(\'\')}}">{{ someVariable }}</div>';
        $compiler = $this->createCompiler($html);
        $compiler->setBanner('This file was generated using VueToTwig');

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function setBannerAddsMultipleCommentsToTheTopOfTheTwigFile()
    {
        $html = '<template><div>{{ someVariable }}</div></template>';
        $expected = '{#
 # This file was generated using VueToTwig
 # Source: assets/js/SomeComponent.vue
 #}
<div class="{{class|default(\'\')}}">{{ someVariable }}</div>';

        $compiler = $this->createCompiler($html);
        $compiler->setBanner([
            'This file was generated using VueToTwig',
            'Source: assets/js/SomeComponent.vue',
        ]);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function canHandleUTF8()
    {
        $html = '<template><div>Äöü: 10,00€</div></template>';
        $expected = '<div class="{{class|default(\'\')}}">Äöü: 10,00€</div>';

        $compiler = $this->createCompiler($html);
        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
