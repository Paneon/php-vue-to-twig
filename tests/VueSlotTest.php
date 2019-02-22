<?php

namespace Paneon\VueToTwig\Tests;

class VueSlotTest extends AbstractTestCase
{
    /**
     * @dataProvider dataProvider
     * @throws \Exception
     */
    public function testComponent($html, $expected)
    {
        $compiler = $this->createCompiler($html);

        $compiler->registerComponent('ChildComponent', '/templates/ChildComponent.twig');

        $actual = $compiler->convert();

        $this->assertEqualHtml($expected, $actual);
    }

    public function dataProvider()
    {
        return $this->loadFixturesFromDir('vue-slot');
    }
}
