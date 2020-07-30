<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class VueCloakTest extends AbstractTestCase
{
    /**
     * @throws Exception
     */
    public function testVueCloak()
    {
        $vueTemplate = '<template><div v-cloak></div></template>';

        $expected = '<div v-cloak class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}"></div>';

        $compiler = $this->createCompiler($vueTemplate);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
