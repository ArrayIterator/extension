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

/**
 * PSR-0 Autoloader file for case insensitive
 */
namespace ArrayIterator {

    /**
     * PSR-0 Fix for lower case files
     */
    spl_autoload_register(function ($className) {
        static $files;

        $nameSpace = __NAMESPACE__ . '\\Extension\\';
        if (stripos($className, $nameSpace) !== 0) {
            return;
        }

        if (!isset($files)) {
            /**
             * Listing cache data of files
             *
             * @var \SplFileInfo $fInfo
             */
            foreach (new \FilesystemIterator(
                __DIR__ . DIRECTORY_SEPARATOR . 'src'. DIRECTORY_SEPARATOR,
                \FilesystemIterator::SKIP_DOTS
                 | \FilesystemIterator::KEY_AS_FILENAME
                 | \FilesystemIterator::CURRENT_AS_FILEINFO
            ) as $key => $fInfo) {
                if (!$fInfo->isFile() || $fInfo->getExtension() !== 'php') {
                    continue;
                }
                $key = strtolower(substr($key, 0, -4));
                $files[$key] = $fInfo->getRealPath();
            }
        }

        $className = strtolower(substr($className, strlen($nameSpace)));
        if (isset($files[$className])) {
            /** @noinspection PhpIncludeInspection */
            require $files[$className];
        }
    });
}
