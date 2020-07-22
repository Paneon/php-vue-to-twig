<?php

namespace Paneon\VueToTwig\Tests;

use Exception;

class VueSlotTest extends AbstractTestCase
{
    /**
     * @dataProvider dataProvider
     *
     * @param mixed $html
     * @param mixed $expected
     *
     * @throws Exception
     */
    public function testComponent($html, $expected)
    {
        $compiler = $this->createCompiler($html);

        $compiler->registerComponent('ChildComponent', '/templates/ChildComponent.twig');

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return $this->loadFixturesFromDir('vue-slot');
    }
}
