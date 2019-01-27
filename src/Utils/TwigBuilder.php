<?php

namespace Paneon\VueToTwig\Utils;

class TwigBuilder
{
    protected const OPEN = 0;
    protected const CLOSE = 1;

    protected $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'tag_comment' => ['{#', '#}'],
            'tag_block' => ['{%', '%}'],
            'tag_variable' => ['{{', '}}'],
            'whitespace_trim' => '-',
            'interpolation' => ['#{', '}'],
        ], $options);
    }

    public function createVariable($name, $assignment)
    {
        return $this->createBlock('set '.$name.' = '.$assignment);
    }

    public function createIf(string $condition)
    {
        return $this->createBlock('if '.$condition);
    }

    public function createElseIf(string $condition)
    {
        return $this->createBlock('elseif '.$condition);
    }

    public function createElse()
    {
        return $this->createBlock('else');
    }

    public function createEndIf()
    {
        return $this->createBlock('endif');
    }

    public function createForItemInList(string $item, string $list)
    {
        return $this->createBlock('for '.$item.' in '.$list);
    }

    public function createForKeyInList(string $key, string $list)
    {
        return $this->createBlock('for '.$key.' in '.$list);
    }

    public function createFor(string $list, ?string $item = null, ?string $key = null)
    {
        if($item !== null && $key !== null) {
            return $this->createBlock( 'for '.$key.', '.$item.' in '.$list);
        }
        elseif($item !== null) {
            return $this->createForItemInList($item, $list);
        }
        elseif($key !== null) {
            return $this->createForKeyInList($key, $list);
        }

        return null;
    }
    
    public function createEndFor()
    {
        return $this->createBlock('endfor');
    }

    public function createComment(string $comment)
    {
        return $this->options['tag_comment'][self::OPEN].' '.$comment.' '.$this->options['tag_comment'][self::CLOSE];
    }

    public function createMultilineComment(array $comments)
    {
        return $this->options['tag_comment'][self::OPEN].' '.$comments.' '.$this->options['tag_comment'][self::CLOSE];
    }

    public function createBlock($content)
    {
        return $this->options['tag_block'][self::OPEN].' '.$content.' '.$this->options['tag_block'][self::CLOSE];
    }
}
