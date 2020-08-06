<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class VuePreTest extends AbstractTestCase
{
    /**
     * @dataProvider dataProvider

     *
     * @throws Exception
     */
    public function testVuePre($template, $expected)
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
                '<template><div v-pre><div v-if="true">{{ 42 }}</div></div></template>',
                '{% verbatim %}<div><div v-if="true">{{ 42 }}</div></div>{% endverbatim %}',
            ],
            [
                '<template><div v-pre v-if="true" class="foo"><h2 v-if="headline">{{ headline }}</h2><div class="bar"><Spinner><span v-if="button">{{ button }}</span></Spinner></div></div></template>',
                '{% verbatim %}<div v-if="true" class="foo"><h2 v-if="headline">{{ headline }}</h2><div class="bar"><spinner><span v-if="button">{{ button }}</span></spinner></div></div>{% endverbatim %}',
            ],
        ];
    }
}
