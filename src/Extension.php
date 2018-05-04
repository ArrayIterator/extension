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
 * Class Extension
 * @package ArrayIterator\Extension
 *
 * <b>Abstract Extension Template</b> for reference of extension.
 */
abstract class Extension implements ExtensionInterface
{
    /**
     * Extension Name
     *
     * @var string
     */
    protected $extensionName = '';

    /**
     * Extension Version
     *
     * @var string
     */
    protected $extensionVersion = '';

    /**
     * Extension Description
     *
     * @var string
     */
    protected $extensionDescription = '';

    /**
     * Extension info from constructor stored
     *
     * @var ExtensionInfo
     */
    protected $extensionInfo;

    /**
     * Extension constructor.
     * @param ExtensionInfo $info
     */
    public function __construct(ExtensionInfo $info)
    {
        $this->extensionInfo = $info;
        $this->onConstruct($info);
    }

    /**
     * Method when constructor Called
     *
     * @param ExtensionInfo $info <p>
     * ExtensionInfo object default representation to normalize properties
     * </p>
     * @return void
     */
    protected function onConstruct(ExtensionInfo $info)
    {
        if (!is_string($this->extensionVersion)) {
            $this->extensionVersion = $info->getVersion();
        }

        if (!is_string($this->extensionName) || $this->extensionName === '') {
            $this->extensionName = $info->getName();
        }

        if (!is_string($this->extensionDescription)) {
            $this->extensionDescription = $info->getDescription();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return $this->extensionName;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersionString() : string
    {
        return $this->extensionVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription() : string
    {
        return $this->extensionDescription;
    }
}
