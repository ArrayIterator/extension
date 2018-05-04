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
 * Class ExtensionInfo
 * @package ArrayIterator\Extension
 */
final class ExtensionInfo
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $classPath;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var bool
     */
    protected $strictMode;

    /**
     * ExtensionInfo constructor.
     * @param \ReflectionClass $reflection
     * @param bool $strictMode
     */
    public function __construct(\ReflectionClass $reflection, bool $strictMode = false)
    {
        $this->strictMode = $strictMode;
        $this->parseForInfo($reflection);
    }

    /**
     * Parse Info for Reflection
     *
     * @param \ReflectionClass $reflection
     */
    protected function parseForInfo(\ReflectionClass $reflection)
    {
        if (!$reflection->isSubclassOf(ExtensionInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'ReflectionClass must be object contain of sub class %s',
                    ExtensionInterface::class
                )
            );
        }

        if (! $reflection->isInstantiable()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'ReflectionClass must be as an instantiable object of %s',
                    ExtensionInterface::class
                )
            );
        }

        if ($reflection->isAnonymous()) {
            throw new \InvalidArgumentException(
                'ReflectionClass can not be an anonymous object'
            );
        }

        $this->className = $reflection->getName();
        $this->classPath = $reflection->getFileName();
        $this->name = $reflection->getShortName();
        $this->version = '';
        $this->description = '';

        $prop = $reflection->getDefaultProperties();
        unset($reflection);

        if (isset($prop['extensionVersion'])
            && (is_string($prop['extensionVersion']) || is_numeric($prop['extensionVersion']))
        ) {
            $this->version = (string) $prop['extensionVersion'];
        } elseif (isset($prop['version'])
            && (is_string($prop['version']) || is_numeric($prop['version']))
        ) {
            $this->version = (string) $prop['version'];
        }

        if (isset($prop['extensionDescription']) && is_string($prop['extensionDescription'])) {
            $this->description = $prop['extensionDescription'];
        } elseif (isset($prop['description']) && is_string($prop['description'])) {
            $this->description = $prop['description'];
        }

        if (!empty($prop['extensionName']) && is_string($prop['extensionName'])) {
            $this->name = $prop['extensionName'];
        } elseif (!empty($prop['name']) && is_string($prop['name'])) {
            $this->name = $prop['name'];
        }

        unset($prop);
    }

    /**
     * Check if on Strict Mode
     *
     * @return bool
     */
    public function isStrictMode() : bool
    {
        return $this->strictMode;
    }

    /**
     * Check if provide valid reflection
     *
     * @return bool
     */
    public function isValid() : bool
    {
        return $this->className !== null;
    }

    /**
     * Get default description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get default version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get default version
     *
     * @return string
     */
    public function getName()
    {
        return $this->className;
    }

    /**
     * Get class file path
     *
     * @return string
     */
    public function getClassPath()
    {
        return $this->classPath;
    }

    /**
     * Get class name
     *
     * @return string
     */
    public function getClassName() : string
    {
        return $this->className;
    }

    /**
     * Magic Method Sleep
     *
     * @return array
     */
    public function __sleep() : array
    {
        return [
            'className',
            'strictMode'
        ];
    }

    /**
     * Magic Method Wake Up
     */
    public function __wakeup()
    {
        if ($this->className) {
            try {
                $this->parseForInfo(new \ReflectionClass($this->className));
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'ReflectionClass must be object contain of sub class %s',
                        ExtensionInterface::class
                    )
                );
            }
        }
    }
}
