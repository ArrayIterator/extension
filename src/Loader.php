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
 *
 * Extension Loader for main extension object core.
 */
class Loader
{
    /**
     * Extension directory to check.
     *
     * @var string
     */
    protected $extensionsDirectory;

    /**
     * Extension Parser.
     *
     * @var ParserInterface
     */
    protected $parser;

    /**
     * Stored data about extension
     *
     * @var \SplFixedArray|ExtensionInfo[]|ExtensionInterface[]
     */
    protected $stack;

    /**
     * Determine if on strict mode.
     *
     * @var bool
     */
    protected $strictMode = false;

    /**
     * List of original identifiers.
     *
     * @var string[]
     */
    protected $keysNormal = null;

    /**
     * List of lower case identifier. Values as offset stack.
     *
     * @var int[]
     */
    protected $keysLower = null;

    /**
     * List of loaded extensions.
     *
     * @var string[]|null
     */
    protected $loaded = null;

    /**
     * List of class name duplication extension detect by parser.
     * Key name as original string identifier.
     *
     * @var array|array[]|string[][]
     */
    protected $duplications = [];

    /**
     * Loader constructor.
     * @param string $extensionsDirectory <p>
     * Extensions directory to crawl.
     * </p>
     * @param bool $strictMode [optional] <p>
     * Determine about use <b>Strict Mode</b> or not.
     * </p>
     * @param ParserInterface|null $parser [optional] <p>
     * Object parser to use as extension directory parser & crawler,
     * if <b>NULL</b> @uses Parser as default parser.
     * </p>
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
        $this->extensionsDirectory = (new \SplFileInfo($extensionsDirectory))->getRealPath();
    }

    /**
     * Get parser object instance.
     *
     * @return ParserInterface
     */
    public function getParser() : ParserInterface
    {
        return $this->parser;
    }

    /**
     * Get extensions directory.
     *
     * @return string realpath extensions directory.
     */
    public function getExtensionsDirectory() : string
    {
        return $this->extensionsDirectory;
    }

    /**
     * Get Strict Mode information.
     *
     * @return bool is on Strict Mode or not.
     */
    public function isStrictMode() : bool
    {
        return $this->strictMode;
    }

    /**
     * Start parsing extensions from given extensions directory.
     *
     * @uses FilesystemIterator <p>
     * To iterate scan of directory.
     * </p>
     * @return Loader
     */
    public function start() : Loader
    {
        // if stack is not empty, this is mean that
        // has been processed.
        if ($this->stack) {
            return $this;
        }

        // set default properties data
        $this->loaded = [];
        $this->stack = [];
        $this->keysLower = [];
        $this->keysNormal = [];

        $c = 0;
        $existingClass = [];
        /**
         * @var \SplFileInfo $item
         */
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
                        $this->duplications
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
     * Verify if extension is exist.
     *
     * @param string $selector <p>
     * Case insensitive selector, this use base name of extension directory.
     * </p>
     * @return bool TRUE if exist otherwise FALSE
     */
    public function exist(string $selector) : bool
    {
        // if has not been parsed returning false
        if (!$this->stack) {
            return false;
        }

        return isset($this->keysLower[strtolower($selector)]);
    }

    /**
     * Verify if extension loaded.
     *
     * @param string $selector <p>
     * Case insensitive selector, this use base name of extension directory.
     * </p>
     * @return bool TRUE if loaded otherwise FALSE.
     */
    public function isLoaded(string $selector) : bool
    {
        // if has not been parsed returning false
        if (!$this->stack) {
            return false;
        }

        return isset($this->loaded[strtolower($selector)]);
    }

    /**
     * Get available extensions selector.
     *
     * @return string[] list of selector / extensions base name.
     */
    public function getAllAvailableExtensions() : array
    {
        return $this->start()->keysNormal;
    }

    /**
     * Get list of duplicate classes while on parsing process
     * key name as base of directory.
     *
     * @return array|string[][] List of duplicate class name. Key name as original string identifier.
     */
    public function getDuplications() : array
    {
        return $this->duplications;
    }

    /**
     * Load the extension by selector.
     *
     * @param string $selector <p>Input string selector base name.</p>
     * @return ExtensionInterface instance of extension.
     * @throws ExtensionNotFoundException if extension does not exists.
     * @see Loader::instantiateExtension()
     */
    public function load(string $selector)
    {
        // if stack is empty, start the parsing process.
        if (!$this->stack) {
            $this->start();
        }
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
     * Instantiate extension.
     *
     * @param string $extensionClassName <p>
     * Extension class name.
     * </p>
     * @param ExtensionInfo $info <p>
     * ExtensionInfo as default object info representation.
     * </p>
     * @return ExtensionInterface instance of object.
     */
    protected function instantiateExtension(
        string $extensionClassName,
        ExtensionInfo $info
    ) : ExtensionInterface {
        return new $extensionClassName($info);
    }

    /**
     * Magic Method __sleep keep the data when object being serialize.
     *
     * @return array represent as object properties need to be keep.
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
     * Magic Method __wakeup() process when serialized object being unserialize.
     *
     * @return void
     */
    public function __wakeup()
    {
        // check if stack if empty and extension as loaded
        if (!$this->stack && is_array($this->loaded)) {
            $this->start();
            foreach ($this->loaded as $key => $bool) {
                $this->load($key);
            }
        }
    }
}
