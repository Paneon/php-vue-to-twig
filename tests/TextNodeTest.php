<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class TextNodeTest extends AbstractTestCase
{
    /**
     * @throws Exception
     */
    public function testTextNode()
    {
        $html = '<template><div>foo {{ bar.trim }}</div></template>';
        $expected = '<div class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}">foo {{ bar|trim }}</div>';

        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function testTextNodeNoReplace()
    {
        $html = '<template><div>foo.trim {{ \'foo === bar\' }}</div></template>';
        $expected = '<div class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}">foo.trim {{ \'foo === bar\' }}</div>';

        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function testTextNodeDontCloseInQuote()
    {
        $html = '<template><div>{{ \'}}\' || foo.length }}</div></template>';
        $expected = '<div class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}">{{ \'}}\' or foo|length }}</div>';

        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function testTextNodeWithTemplateString()
    {
        $html = '<template><div>{{ `Var = ${var}` }}</div></template>';
        $expected = '<div class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}">{{ \'Var = \' ~ var ~ \'\' }}</div>';

        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function testTextNodeNumbers()
    {
        $html = '<template><div>{{ 1 + 1 }}</div></template>';
        $expected = '<div class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}">{{ 1 + 1 }}</div>';

        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
