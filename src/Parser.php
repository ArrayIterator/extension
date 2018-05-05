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
 * Class Parser
 * @package ArrayIterator\Extension
 *
 * Extension Parser to parse and detect path of extension directory.
 */
class Parser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(
        string $directory,
        bool $strict = false,
        array $existingClass = [],
        array &$duplication = []
    ) {
        $parser = $this->scanDirectory(
            $directory,
            $strict,
            $existingClass,
            $duplication
        );
        if ($parser) {
            $parser = new ExtensionInfo($parser, $strict);
        }

        return $parser;
    }

    /**
     * Scan directory extensions placed.
     *
     * @param string $directory <p>
     * Extension directory to be crawl.
     * </p>
     * @param bool $strict      <p>
     * Determine if on <b>Strict Mode</b> or not
     * </p>
     * @param array $existingClass <p>List of existing class.</p>
     * @param array $duplication <p>
     * array for injection reference duplications.
     * </p>
     * @return null|\ReflectionClass returning \ReflectionClass or NULL if invalid.
     */
    protected function scanDirectory(
        string $directory,
        bool $strict,
        array $existingClass = [],
        array &$duplication = []
    ) {
        $directory = rtrim(
            Utility::normalizeDirectorySeparator($directory),
            DIRECTORY_SEPARATOR
        );

        $baseName  = basename($directory);
        $ref       = null;
        $className = Utility::parseClassName($baseName);
        if ($className) {
            $className = $directory .  DIRECTORY_SEPARATOR . $className . '.php';
            if (is_file($className) && is_readable($className)) {
                $ref = $this->parseFile($className);
            }
            clearstatcache(true, $className);
            // check if in strict mode
            if ($strict && ($ref === null || strtolower($ref->getShortName()) !== strtolower($className))) {
                unset($ref);
                return null;
            }
        } elseif ($strict) {
            return null;
        }

        if ($ref) {
            if (in_array($ref->getName(), $existingClass)) {
                $duplication[$baseName] = [$ref->getName()];
                $ref = null;
            }

            return $ref;
        }

        /**
         * @var \SplFileInfo $item
         */
        foreach (new \FilesystemIterator(
            $directory,
            \FilesystemIterator::CURRENT_AS_FILEINFO
             | \FilesystemIterator::KEY_AS_FILENAME
             | \FilesystemIterator::SKIP_DOTS
        ) as $fileName => $item) {
            if ($fileName === $className
                || $item->isFile() === false
                || $item->getExtension() !== 'php'
                || $item->isReadable() === false
            ) {
                continue;
            }

            if (($ref = $this->parseFile($item->getRealPath()))) {
                if (!in_array($ref->getName(), $existingClass)) {
                    break;
                }
                if (!isset($duplication[$baseName])) {
                    $duplication[$baseName] = [];
                }
                $duplication[$baseName][] = $ref->getName();
                $ref = null;
            }
        }

        return $ref;
    }

    /**
     * Doing process parse of extension file.
     *
     * @param string $target <p>
     * Target file to be parse.
     * </p>
     * @return null|\ReflectionClass returning \ReflectionClass or NULL if invalid.
     */
    protected function parseFile(string $target)
    {
        if (substr($target, -4) !== '.php' || !is_file($target)) {
            return null;
        }

        $data = php_strip_whitespace($target);
        if (strtolower(substr($data, 0, 5)) !== '<?php'
            || preg_replace('~\<\?php\s*|[\s]|\s*\?\>~i', '', $data) === ''
        ) {
            return null;
        }
        $data = preg_replace(
            '/^\<\?php\s+declare[^;]+\;\s*/smi',
            "<?php\n",
            $data
        );
        preg_match(
            '~^\<\?php
                \s+namespace\s+
                (
                    [a-z\_][a-z0-9\_]{0,}
                    (?:[\\\]?[a-z\_][a-z0-9\_]{0,}){0,}
                )\s*[;]+
            ~smix',
            $data,
            $namespace
        );

        $namespace = empty($namespace[1])  ? '' : $namespace[1].'\\';
        if ($namespace === '' && preg_match('~^\<\?php\s*namespace\s+~i', $data)) {
            unset($data);
            return null;
        }

        preg_match(
            '~
                \s+
                class\s+([a-z\_][a-z0-9\_]{0,})
                (?:
                    \s+
                    (?:
                        implements\s+
                        \\\?[a-z\_][a-z0-9\_]{0,}
                        (?:\s*[\,]\s*[\\\]?[a-z\_][a-z0-9\_]{0,}){0,}
                        | extends\s+\\\?[a-z\_][a-z0-9\_]{0,}
                            (?:[\\\][a-z\_][a-z0-9\_]{0,}){0,}
                    )
                )
            ~xi',
            $data,
            $class
        );
        unset($data);

        if (empty($class[1])) {
            return null;
        }

        $class = $namespace . $class[1];
        try {
            $ref = new \ReflectionClass($class);
            if (! $ref->isAnonymous()
                && $ref->isInstantiable()
                && $ref->isSubclassOf(ExtensionInterface::class)
            ) {
                return $ref;
            }
        } catch (\Exception $e) {
        }

        set_error_handler(function () {
            error_clear_last();
            throw new \Exception();
        });

        $ref = null;
        try {
            /** @noinspection PhpIncludeInspection */
            include_once $target;
            $ref = new \ReflectionClass($class);
            if ($ref->isAnonymous()
                || ! $ref->isInstantiable()
                || ! $ref->isSubclassOf(ExtensionInterface::class)
            ) {
                $ref = null;
            }
        } catch (\Exception $e) {
        } catch (\Throwable $e) {
        } finally {
            unset($e);
            // pass
            restore_error_handler();
        }

        return $ref;
    }

    /**
     * Implementation of interface \Serializable, when object serialize.
     *
     * @return string serialized data.
     */
    public function serialize() : string
    {
        return serialize([]);
    }

    /**
     * Implementation of interface \Serializable, when object serialized unserialize.
     *
     * @param string $serialized <p>
     * Serialized data.
     * </p>
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        return;
    }
}
