<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class VuePreTest extends AbstractTestCase
{
    /**
     * @throws Exception
     */
    public function testVueCloak()
    {
        $vueTemplate = '<template><div v-pre><div v-if="true">{{ 42 }}</div></div></template>';

        $expected = '<div><div v-if="true">{{ 42 }}</div></div>';

        $compiler = $this->createCompiler($vueTemplate);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
