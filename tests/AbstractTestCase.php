<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Tests;

use DirectoryIterator;
use DOMDocument;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Paneon\VueToTwig\Compiler;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @throws Exception
     */
    protected function createCompiler(string $template): Compiler
    {
        $document = $this->createDocumentWithHtml($template);
        $compiler = new Compiler($document, $this->createLogger());

        return $compiler;
    }

    /**
     * @throws Exception
     */
    protected function createLogger(): Logger
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__ . '/../var/dev/test.log'));

        return $logger;
    }

    /**
     * @param $expected
     * @param $actual
     */
    protected function assertEqualHtml($expected, $actual)
    {
        $this->assertEquals(
            $this->normalizeHtml($expected),
            $this->normalizeHtml($actual)
        );
    }

    protected function createDocumentWithHtml(string $html): DOMDocument
    {
        $vueDocument = new DOMDocument('1.0', 'utf-8');
        @$vueDocument->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return $vueDocument;
    }

    protected function normalizeHtml(string $html): string
    {
        $html = preg_replace('/(\s)+/s', '\\1', $html);
        $html = str_replace("\n", '', $html);

        // Trim node text
        $html = preg_replace('/>[^\S ]+/s', '>', $html);
        $html = preg_replace('/[^\S ]+</s', '<', $html);

        $html = preg_replace('/> </s', '><', $html);
        $html = preg_replace('/} </s', '}<', $html);
        $html = preg_replace('/> {/s', '>{', $html);
        $html = preg_replace('/} {/s', '}{', $html);

        return $html ?? '';
    }

    protected function loadFixturesFromDir(string $dir): array
    {
        $fixtureDir = __DIR__ . '/fixtures/' . $dir;

        $cases = [];

        foreach (new DirectoryIterator($fixtureDir) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->getExtension() !== 'vue') {
                continue;
            }

            // Skip files which have an "x" prefix
            if (substr($fileInfo->getBasename(), 0, 1) === 'x') {
                continue;
            }

            $templateFile = $fileInfo->getPathname();
            $twigFile = str_replace('.vue', '.twig', $templateFile);

            $template = file_get_contents($templateFile);
            $expected = file_get_contents($twigFile);

            $cases[$fileInfo->getBasename('.vue')] = [
                $template,
                $expected,
            ];
        }

        return $cases;
    }
}
