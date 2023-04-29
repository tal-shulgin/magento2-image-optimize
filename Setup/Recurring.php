<?php

declare(strict_types=1);

namespace Mageit\ImageOptimize\Setup;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Psr\Log\LoggerInterface;

class Recurring implements InstallSchemaInterface
{
    public function __construct(
        private Dir $moduleDir,
        private File $filesystem,
        private LoggerInterface $logger,
        private DirectoryList $directoryList
    ) {}

    /**
     * @inheritDoc
     * @throws FileSystemException
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $dirVar = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) .
            '/mageit/bin/';
        $dirModule = $this->moduleDir->getDir('Mageit_ImageOptimize') . '/bin/';
        try {
            $this->filesystem->deleteDirectory($dirVar);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        $this->filesystem->createDirectory($dirVar);
        $this->filesystem->copy($dirModule . 'gifsicle', $dirVar . 'gifsicle');
        $this->filesystem->copy($dirModule . 'guetzli', $dirVar . 'guetzli');
        $this->filesystem->copy($dirModule . 'jpegoptim', $dirVar . 'jpegoptim');
        $this->filesystem->copy($dirModule . 'jpegtran', $dirVar . 'jpegtran');
        $this->filesystem->copy($dirModule . 'optipng', $dirVar . 'optipng');
        $this->filesystem->changePermissionsRecursively(
            $dirVar,
            0755,
            0777
        );
        $setup->endSetup();
    }
}
