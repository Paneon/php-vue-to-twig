<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class DataTwigRemoveTest extends AbstractTestCase
{
    /**
     * @throws Exception
     */
    public function testDataTwigRemove()
    {
        $vueTemplate = '<template><div><span data-twig-remove>dummy</span></div></template>';

        $expected = '<div class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}"></div>';

        $compiler = $this->createCompiler($vueTemplate);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function testDataTwigRemoveWithIf()
    {
        $vueTemplate = '<template><div><span v-if="true" data-twig-remove>dummy</span></div></template>';

        $expected = '<div class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}"></div>';

        $compiler = $this->createCompiler($vueTemplate);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }
}
