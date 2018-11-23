<?php declare(strict_types=1);

namespace Macavity\VueToTwig;


use DOMDocument;
use Exception;

class Component
{
    /** @var string */
    protected $assetPath;

    /** @var string */
    protected $targetPath;

    /** @var string */
    protected $fileName;

    /** @var DOMDocument */
    protected $document;

    protected $templateHtml;
    protected $data;
    protected $templateElement;
    protected $rootElement;

    /** @var String[] */
    protected $components = [];

    public function __construct(string $assetPath = '', string $targetPath = '')
    {
        $this->assetPath = $assetPath;
        $this->targetPath = $targetPath;

        $this->fileName = '';
        $this->document = new DOMDocument();
    }

    /**
     * @param string $fileName
     *
     * @return Component
     */
    public function loadFile(string $fileName): self
    {
        $this->fileName = $fileName;

        @$this->document->loadHTMLFile($this->assetPath . $fileName);
        $this->templateElement = $this->document->getElementsByTagName('template')->item(0);

        $this->rootElement = $this->getRootNode($this->templateElement);
        $this->templateHtml = $this->getInnerHtml($this->templateElement);

        return $this;
    }

    public function registerComponents($name, $path)
    {
        $this->components[$name] = $path;
    }
}