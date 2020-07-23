<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class VueHtmlTest extends AbstractTestCase
{
    /**
     * @throws Exception
     */
    public function testHtml()
    {
        $component = file_get_contents(__DIR__ . '/fixtures/vue-html/html.vue');
        $expected = file_get_contents(__DIR__ . '/fixtures/vue-html/html.twig');

        if (!$component) {
            self::fail('Component not found.');

            return;
        }

        $compiler = $this->createCompiler($component);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
