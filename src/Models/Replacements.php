<?php

namespace Paneon\VueToTwig\Models;

abstract class Replacements extends BasicEnum
{
    public const DOUBLE_CURLY_OPEN = '{{';
    public const DOUBLE_CURLY_CLOSE = '}}';
    public const GREATER = '>';
    public const SMALLER = '<';
    public const AMPERSAND = '&';

    public static function getSanitizedConstant(string $constant)
    {
        return '__'.$constant.'__';
    }
}
