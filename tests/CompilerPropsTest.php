<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class CompilerPropsTest extends AbstractTestCase
{
    /**
     * @test
     *
     * @throws Exception
     */
    public function registersProperties()
    {
        $component = file_get_contents(__DIR__ . '/fixtures/vue-props/binding-props.vue');
        $expected = file_get_contents(__DIR__ . '/fixtures/vue-props/binding-props.twig');

        if (!$component) {
            self::fail('Component not found.');

            return;
        }

        $compiler = $this->createCompiler($component);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function registersTypeScriptProperties()
    {
        $component = file_get_contents(__DIR__ . '/fixtures/vue-props/binding-props-typescript-default.vue');
        $expected = file_get_contents(__DIR__ . '/fixtures/vue-props/binding-props-typescript-default.twig');

        if (!$component) {
            self::fail('Component not found.');

            return;
        }

        $compiler = $this->createCompiler($component);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
