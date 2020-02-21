<?php

namespace Paneon\VueToTwig\Tests;

class TextNodeTest extends AbstractTestCase
{
    public function testTextNode()
    {
        $html = '<template><div>foo {{ bar.trim }}</div></template>';
        $expected = '<div class="{{class|default(\'\')}}">foo {{ bar|trim }}</div>';

        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function testTextNodeNoReplace()
    {
        $html = '<template><div>foo.trim {{ \'foo === bar\' }}</div></template>';
        $expected = '<div class="{{class|default(\'\')}}">foo.trim {{ \'foo === bar\' }}</div>';

        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function testTextNodeDontCloseInQuote()
    {
        $html = '<template><div>{{ \'}}\' + foo.trim }}</div></template>';
        $expected = '<div class="{{class|default(\'\')}}">{{ \'}}\' ~ foo|trim }}</div>';

        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function testTextNodeWithTemplateString()
    {
        $html = '<template><div>{{ `Var = ${var}` }}</div></template>';
        $expected = '<div class="{{class|default(\'\')}}">{{ \'Var = \' ~ var ~ \'\' }}</div>';

        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
