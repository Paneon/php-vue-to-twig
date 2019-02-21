<?php

namespace Paneon\VueToTwig\Tests;

class CompilerPropsTest extends AbstractTestCase
{
    /** @test */
    public function registersProperties()
    {
        $component = file_get_contents('./fixtures/vue-props/binding-props.vue');
        $expected = file_get_contents('./fixtures/vue-props/binding-props.twig');

        $compiler = $this->createCompiler($component);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);

    }
}
