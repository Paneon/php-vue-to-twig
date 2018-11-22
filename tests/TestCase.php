<?php declare(strict_types=1);

namespace Macavity\VueToTwig\Tests;

use DOMDocument;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function assertEqualHtml( $expectedResult, $result ): void
    {
        $expectedResult = $this->normalizeHtml( $expectedResult );
        $result = $this->normalizeHtml( $result );

        $this->assertEquals( $expectedResult, $result );
    }

    protected function createDocumentWithHtml(string $html): DOMDocument
    {
        $document = new DOMDocument();
        @$document->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return $document;
    }

    protected function normalizeHtml( $html ): string
    {
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

        return trim($html);
    }
}