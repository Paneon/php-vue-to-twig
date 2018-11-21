<?php

namespace Macavity\VueToTwig\Tests;


class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $expectedResult
     * @param string $result
     */
    protected function assertEqualHtml( $expectedResult, $result ) {
        $expectedResult = $this->normalizeHtml( $expectedResult );
        $result = $this->normalizeHtml( $result );

        $this->assertEquals( $expectedResult, $result );
    }

    /**
     * @param string $html
     *
     * @return string HTML
     */
    protected function normalizeHtml( $html ) {
        $html = preg_replace( '/<!--.*?-->/', '', $html );
        $html = preg_replace( '/\s+/', ' ', $html );
        // Trim node text
        $html = str_replace( '> ', ">", $html );
        $html = str_replace( ' <', "<", $html );
        // Each tag (open and close) on a new line
        $html = str_replace( '>', ">\n", $html );
        $html = str_replace( '<', "\n<", $html );
        // Remove duplicated new lines
        $html = str_replace( "\n\n", "\n", $html );

        $html = trim( $html );
        return $html;
    }
}