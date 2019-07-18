<?php

namespace Paneon\VueToTwig\Tests;

class CompilerTwigBlockTest extends AbstractTestCase
{
    /** @test */
    public function registersProperties()
    {
        $component = file_get_contents(__DIR__.'/fixtures/twig-block/twig-block.vue');
        $expected = file_get_contents(__DIR__.'/fixtures/twig-block/twig-block.twig');

        if(!$component){
            self::fail('Component not found.');
            return;
        }

        $compiler = $this->createCompiler($component);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);

    }
}
