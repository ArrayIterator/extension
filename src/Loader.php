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

use FilesystemIterator;

/**
 * Class Loader
 * @package ArrayIterator\Extension
 */
class Loader
{
    /**
     * @var string
     */
    protected $extensionsDirectory;

    /**
     * @var ParserInterface
     */
    protected $parser;

    /**
     * @var \SplFixedArray|ExtensionInfo[]
     */
    protected $stack;

    /**
     * @var bool
     */
    protected $strictMode = false;

    /**
     * @var string[]
     */
    protected $keysNormal = null;

    /**
     * @var int[]
     */
    protected $keysLower = null;

    /**
     * @var array
     */
    protected $loaded = null;

    /**
     * @var array
     */
    protected $duplication = [];

    /**
     * Loader constructor.
     * @param string $extensionsDirectory
     * @param bool $strictMode
     * @param ParserInterface|null $parser
     */
    public function __construct(
        string $extensionsDirectory,
        bool $strictMode = false,
        ParserInterface $parser = null
    ) {
        $spl = new \SplFileInfo($extensionsDirectory);
        if (!$spl->isDir()) {
            throw new \RuntimeException(
                sprintf(
                    'Directory %s is not exists',
                    $extensionsDirectory
                )
            );
        }

        $this->stack = null;
        $this->strictMode = $strictMode;
        $this->parser = $parser ?: new Parser();
        $this->extensionsDirectory = $spl->getRealPath();
    }

    /**
     * @param string $directory
     * @return string
     */
    protected function prepareDirectory(string $directory) : string
    {
        return (new \SplFileInfo($directory))->getRealPath();
    }

    /**
     * @return ParserInterface
     */
    public function getParser() : ParserInterface
    {
        return $this->parser;
    }

    /**
     * Get extensions directory
     *
     * @return string
     */
    public function getExtensionsDirectory() : string
    {
        return $this->extensionsDirectory;
    }

    /**
     * @return bool
     */
    public function isStrictMode() : bool
    {
        return $this->strictMode;
    }

    /**
     * @return Loader
     */
    public function start() : Loader
    {
        if ($this->stack) {
            return $this;
        }
        $this->loaded = [];
        $this->stack = [];
        $this->keysLower = [];
        $this->keysNormal = [];

        /**
         * @var \SplFileInfo $item
         */
        $c = 0;
        $existingClass = [];
        foreach (new FilesystemIterator(
            $this->extensionsDirectory,
            FilesystemIterator::CURRENT_AS_FILEINFO
            | FilesystemIterator::KEY_AS_FILENAME
            | FilesystemIterator::SKIP_DOTS
        ) as $fileName => $item) {
            if ($item->isDir()) {
                $name = basename($fileName);
                $lowerName = strtolower($name);
                if (isset($this->keysLower[$lowerName])) {
                    continue;
                }
                $parsed = $this
                    ->parser
                    ->parse(
                        $this->extensionsDirectory
                        . DIRECTORY_SEPARATOR
                        . $fileName,
                        $this->strictMode,
                        $existingClass,
                        $this->duplication
                    );
                if ($parsed) {
                    array_push($existingClass, $parsed->getClassName());
                    $this->keysNormal[$c] = $name;
                    $this->keysLower[$lowerName] = $c;
                    $this->stack[$c] = $parsed;
                    $c++;
                }
            }
        }

        unset($parsed, $existingClass);
        $this->stack = \SplFixedArray::fromArray($this->stack);

        return $this;
    }

    /**
     * Verify if Extension exist
     *
     * @param string $selector
     * @return bool
     */
    public function exist(string $selector) : bool
    {
        !$this->stack && $this->start();
        return isset($this->keysLower[strtolower($selector)]);
    }

    /**
     * Verify if extension loaded
     *
     * @param string $selector
     * @return bool
     */
    public function isLoaded(string $selector) : bool
    {
        // if has not been parsed returning false
        if (!$this->stack) {
            return false;
        }
        $selector = strtolower($selector);
        return isset($this->loaded[$selector]);
    }

    /**
     * Get available extensions selector
     *
     * @return string[]
     */
    public function getAllAvailableExtensions() : array
    {
        return $this->start()->keysNormal;
    }

    /**
     * Load extension
     *
     * @param string $selector
     * @return ExtensionInfo|mixed
     */
    public function load(string $selector)
    {
        if (!$this->exist($selector)) {
            throw new ExtensionNotFoundException($selector);
        }

        $selector = strtolower($selector);
        $offset = $this->keysLower[$selector];
        if (isset($this->loaded[$selector])) {
            return $this->stack[$offset];
        }

        $this->stack[$offset]    = $this->instantiateExtension(
            $this->stack[$offset]->getClassName(),
            $this->stack[$offset]
        );
        $this->loaded[$selector] = $this->keysNormal[$offset];
        return $this->stack[$offset];
    }

    /**
     * Instantiate extension
     *
     * @param string $extensionClassName
     * @param ExtensionInfo $info
     * @return ExtensionInterface
     */
    protected function instantiateExtension(
        string $extensionClassName,
        ExtensionInfo $info
    ) : ExtensionInterface {
        return new $extensionClassName($info);
    }

    /**
     * Magic method __sleep() when object @uses serialize()
     *
     * @return array
     */
    public function __sleep() : array
    {
        return [
            'extensionsDirectory',
            'strictMode',
            'parser',
            'loaded'
        ];
    }

    /**
     * * Magic method __sleep() when object @uses unserialize()
     */
    public function __wakeup()
    {
        if (is_array($this->loaded)) {
            $this->start();
            foreach ($this->loaded as $key => $bool) {
                $this->load($key);
            }
        }
    }
}
