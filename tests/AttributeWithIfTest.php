<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class AttributeWithIfTest extends AbstractTestCase
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
            [
                '<template><form><input type="checkbox" :checked="foo || bar"></form></template>',
                '<form class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}"><input type="checkbox" {% if foo or bar %}checked="checked"{% endif %}></form>',
            ],
            [
                '<template><form><input type="checkbox" :checked="foo === 1"></form></template>',
                '<form class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}"><input type="checkbox" {% if foo == 1 %}checked="checked"{% endif %}></form>',
            ],
            [
                '<template><form><input type="checkbox" :disabled="foo === 0" :checked="foo === 1"></form></template>',
                '<form class="{{ class|default(\'\') }}" style="{{ style|default(\'\') }}"><input type="checkbox" {% if foo == 0 %}disabled="disabled"{% endif %} {% if foo == 1 %}checked="checked"{% endif %}></form>',
            ],
        ];
    }
}
