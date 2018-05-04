<?php
/**
 * MIT License
 *
 * Copyright (c) 2018, ArrayIterator
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace ArrayIterator\Extension;

/**
 * Class Utility
 * @package ArrayIterator\Extension
 */
class Utility
{
    /**
     * Normalize Directory Separator
     *
     * @param string $sep
     * @return string
     */
    public static function normalizeDirectorySeparator(string $sep) : string
    {
        return preg_replace(
            '~(\\\|\/)+~',
            DIRECTORY_SEPARATOR,
            $sep
        );
    }

    /**
     * Get class short name
     *
     * @param string $className
     * @return string
     */
    public static function getClassShortName(string $className) : string
    {
        return preg_replace('~^(?:.+\\\)?([^\\\]+)$~', '$1', $className);
    }

    /**
     * Parse Class Name
     *
     * @param string $name
     * @param mixed $match reference
     * @return bool|string return string if valid className
     */
    public static function parseClassName(string $name, &$match = null)
    {
        $match = [];
        preg_match(
            '~^
                \\\?(?P<name>
                    (?:
                        (?P<namespace>
                            [a-zA-Z\_][a-zA-Z\_0-9]{0,}
                            (?:\\\[a-zA-Z\_][a-zA-Z\_0-9]{0,}){0,}
                        )?
                        \\\
                    )?
                    (?P<shortname>[a-zA-Z\_][a-zA-Z\_0-9]{0,})
                )$
                ~x',
            $name,
            $match
        );

        $match = array_filter($match, 'is_string', ARRAY_FILTER_USE_KEY);
        return empty($match) ? false : $match['name'];
    }
}
