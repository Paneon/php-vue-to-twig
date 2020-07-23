<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class VueTextTest extends AbstractTestCase
{
    /**
     * @throws Exception
     */
    public function testHtml()
    {
        $component = file_get_contents(__DIR__ . '/fixtures/vue-text/text.vue');
        $expected = file_get_contents(__DIR__ . '/fixtures/vue-text/text.twig');

        if (!$component) {
            self::fail('Component not found.');

            return;
        }

        $compiler = $this->createCompiler($component);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
