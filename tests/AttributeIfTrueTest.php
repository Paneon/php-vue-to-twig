<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class IfAttributesTest extends AbstractTestCase
{
    /**
     * @dataProvider dataProvider
     *
     * @throws Exception
     */
    public function testAttributeIfTrue($template, $expected)
    {
        $compiler = $this->createCompiler($template);

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [
                '<template><form><input type="checkbox" :checked="false"></form></template>',
                '<form class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}"><input type="checkbox"></form>',
            ],
            [
                '<template><form><input type="checkbox" :checked="foo"></form></template>',
                '<form class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}"><input type="checkbox" {% if foo %}checked="checked"{% endif %}></form>',
            ],
        ];
    }
}
