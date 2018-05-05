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
 * Class ExtensionNotFoundException
 * @package ArrayIterator\Extension
 *
 * Exception object that determine of extension has not found.
 */
class ExtensionNotFoundException extends \RuntimeException
{
    /**
     * Extension name.
     *
     * @var string
     */
    protected $extensionName;

    /**
     * ExtensionNotFoundException constructor.
     * @param string $extensionName <p>
     * Extension Name information selector.
     * </p>
     */
    public function __construct(string $extensionName)
    {
        $message = sprintf(
            'Extension for %s has not exist.',
            $extensionName
        );

        $this->extensionName = $extensionName;
        parent::__construct($message);
    }

    /**
     * Get extension name set from
     *
     * @return string the extension name set from constructor
     */
    public function getExtensionName() : string
    {
        return $this->extensionName;
    }
}
