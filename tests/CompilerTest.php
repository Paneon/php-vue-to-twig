<?php

namespace Macavity\VueToTwig\Tests;

class CompilerTest extends AbstractTestCase
{
    /** @test */
    public function leavesMustacheVariablesIntact()
    {
        $html = '<template><div>{{ someVariable }}</div></template>';
        $expected = '<div>{{ someVariable }}</div>';
        $compiler = $this->createCompiler($html);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
