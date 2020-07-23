<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Models;

use InvalidArgumentException;
use ReflectionException;

abstract class Replacements extends BasicEnum
{
    public const DOUBLE_CURLY_OPEN = '{{';

    public const DOUBLE_CURLY_CLOSE = '}}';

    public const GREATER = '>';

    public const SMALLER = '<';

    public const AMPERSAND = '&';

    public const PIPE = '|';

    public const ATTRIBUTE_NAME_HREF = 'href';

    public const ATTRIBUTE_NAME_ACTION = 'action';

    public const ATTRIBUTE_NAME_SRC = 'src';

    public const ATTRIBUTE_NAME_A_NAME = 'name';

    /**
     * Removes all instances of replacements from target.
     *
     * @throws ReflectionException
     */
    public static function sanitize(string $target): string
    {
        foreach (Replacements::getConstants() as $constant => $value) {
            $target = str_replace($value, Replacements::getSanitizedConstant($constant), $target);
        }

        return $target;
    }

    public static function getSanitizedConstant(string $constant): string
    {
        return '__' . $constant . '__';
    }

    /**
     * Removes all instances of one specified replacement from target.
     */
    public static function sanitizeSingleReplacement(string $target, string $singleReplacement): string
    {
        if (!Replacements::isValidValue($singleReplacement)) {
            throw new InvalidArgumentException(sprintf('%s is not a valid Replacement value.', $singleReplacement));
        }

        $constantName = Replacements::getNameForValue($singleReplacement);

        return str_replace(
            $singleReplacement,
            Replacements::getSanitizedConstant($constantName),
            $target
        );
    }
}
