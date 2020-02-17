<?php

namespace Paneon\VueToTwig\Tests;

class CleanupAttributesTest extends AbstractTestCase
{
    public function testCleanupAttributes()
    {
        $vueTemplate = '<template><div ref="reference">dummy</div></template>';

        $expected = '<div class="{{class|default(\'\')}}">dummy</div>';

        $compiler = $this->createCompiler($vueTemplate);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
