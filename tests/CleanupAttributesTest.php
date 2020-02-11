<?php

namespace Paneon\VueToTwig\Tests;

class CleanupAttributesTest extends AbstractTestCase
{
    public function testCleanupAttributes()
    {
        $vueTemplate = '<template><div v-foo="bar" ref="reference">dummy</div></template>';

        $expected = '<div class="{{class|default(\'\')}}">dummy</div>';

        $compiler = $this->createCompiler($vueTemplate);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
