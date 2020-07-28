<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class CleanupAttributesTest extends AbstractTestCase
{
    /**
     * @throws Exception
     */
    public function testCleanupAttributes()
    {
        $vueTemplate = '<template><div v-foo="bar" ref="reference">dummy</div></template>';

        $expected = '<div class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}">dummy</div>';

        $compiler = $this->createCompiler($vueTemplate);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
