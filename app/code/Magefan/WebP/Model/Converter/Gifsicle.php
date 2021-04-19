<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types = 1);

namespace Magefan\WebP\Model\Converter;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Shell;

class Gifsicle
{
    const CONVERTER = 'gifsicle';

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var string
     */
    private $converter;

    /**
     * @var Shell|null
     */
    private $shell;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var \string[][]
     */
    private $suppliedBinariesInfo = [
        'WINNT' => [
            '\bin\gifsicle.exe'
        ],
        'Windows' => [
            '\bin\gifsicle.exe'
        ],
        'WIN32' => [
            '\bin\gifsicle_32.exe '
        ],
        'Darwin' => [
            '/bin/gifsicle'
        ],
        'SunOS' => [
            '/bin/gifsicle'
        ],
        'FreeBSD' => [
            '/bin/gifsicle'
        ],
        'Linux' => [
            '/bin/gifsicle'
        ]
    ];

    /**
     * Gifsicle constructor.
     * @param Reader $moduleReader
     * @param Shell $shell
     */
    public function __construct(
        Reader $moduleReader,
        Shell $shell,
        File $fileDriver
    ) {
        $this->moduleReader = $moduleReader;
        $this->shell = $shell;
        $this->fileDriver = $fileDriver;
    }

    /**
     * @return string
     */
    private function getModuleDir():string
    {
         return $this->moduleReader->getModuleDir(false, 'Magefan_WebP');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getConverter():string
    {
        if (null === $this->converter) {
            $converter = '';

            $isInstalled = (bool)strrpos($this->shell->execute('gifsicle --version 2>&1'), 'Copyright');
            if (true ===  $isInstalled) {
                $converter = self::CONVERTER;
            } elseif (isset($this->suppliedBinariesInfo[PHP_OS])) {
                $converter = $this->getModuleDir() . $this->suppliedBinariesInfo[PHP_OS][0];
            }
            $this->converter = $converter;
        }

        return $this->converter;
    }

    /**
     * Convert image to webp
     * @param string $image
     * @return bool
     */

    /**
     * Convert image to webp
     * @param string $originImagePath
     * @param string $webpImagePath
     * @param int $quality
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function convert(string $originImagePath, string $webpImagePath, int $quality): bool
    {
        $converter = $this->getConverter();
        if (!$converter) {
            return false;
        }

        $gifDir =  substr($webpImagePath, 0, strrpos($webpImagePath, '/'));
        if (!$this->fileDriver->isExists($gifDir)) {
            $this->fileDriver->createDirectory($gifDir);
        }

        $this->shell->execute($converter . '  -no-ignore-errors --optimize -w '. $originImagePath .' -o '. $webpImagePath);

        return true;
    }
}
