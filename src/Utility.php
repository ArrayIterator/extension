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
 *
 * Object utility and helper.
 */
class Utility
{
    /**
     * Normalize Directory Separator
     *
     * @param string $path <p>Path to normalize.</p>
     * @return string fixed path after path normalized.
     */
    public static function normalizeDirectorySeparator(string $path) : string
    {
        return preg_replace(
            '~(\\\|\/)+~',
            DIRECTORY_SEPARATOR,
            $path
        );
    }

    /**
     * Get class short name or last name without name space
     *
     * @param string|object $input <p>input object or class name to be parse.</p>
     * @return string shortname of class.
     * @throws \InvalidArgumentException <p>if input is not valid.</p>
     */
    public static function getClassShortName($input) : string
    {
        // if input is an object convert it into class string
        if (is_object($input)) {
            $input = get_class($input);
        }

        if (!is_string($input)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument 1 must be as a string or object %s given',
                    gettype($input)
                )
            );
        }

        return preg_replace('~^(?:.+\\\)?([^\\\]+)$~', '$1', $input);
    }

    /**
     * Parse Class Name & object name space from given parameter
     *
     * @param string|object $input <p>input object or class name to be parse</p>
     * @param mixed $arr [optional] <p>
     * If the second parameter arr is present,
     * variables are stored in this variable as array elements instead.
     * </p>
     * @return bool|string full class name if valid class name, otherwise boolean false if invalid.
     * @throws \InvalidArgumentException <p>if input is not valid.</p>
     */
    public static function parseClassName($input, &$arr = null)
    {
        // if input is an object convert it into class string
        if (is_object($input)) {
            $input = get_class($input);
        }

        if (!is_string($input)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument 1 must be as a string or object %s given',
                    gettype($input)
                )
            );
        }

        $arr = [];
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
            $input,
            $arr
        );

        $arr = array_filter($arr, 'is_string', ARRAY_FILTER_USE_KEY);
        return empty($arr) ? false : $arr['name'];
    }
}
