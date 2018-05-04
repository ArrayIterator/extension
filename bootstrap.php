<?php
declare(strict_types=1);

/**
 * Autoload file for case insensitive
 */
namespace ArrayIterator {

    /**
     * Fix for lower case files
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
