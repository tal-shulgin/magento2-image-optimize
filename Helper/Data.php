<?php

namespace Mageit\ImageOptimize\Helper;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Mageit\ImageOptimize\Model\Config\Source\Quality;
use Mageit\ImageOptimize\Model\Config\Source\Status;
use Mageit\ImageOptimize\Model\Optimizer;
use Mageit\ImageOptimize\Model\ResourceModel\Image\Collection as ImageOptimizerCollection;
use Mageit\ImageOptimize\Model\ResourceModel\Image\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class Data extends Config
{
    public const CONFIG_MODULE_PATH = 'mageitimageoptimizer';

    /**
     * Data constructor.
     *
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     * @param DirectoryList         $dir
     * @param Json                  $serialize
     * @param Optimizer             $optimizer
     * @param DriverFile            $driverFile
     * @param IoFile                $ioFile
     * @param Filesystem            $filesystem
     * @param CollectionFactory     $collectionFactory
     */
    public function __construct(
        protected Context $context,
        protected StoreManagerInterface $storeManager,
        protected DirectoryList $dir,
        protected Json $serialize,
        protected Optimizer $optimizer,
        protected DriverFile $driverFile,
        protected IoFile $ioFile,
        protected Filesystem $filesystem,
        protected CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $storeManager, $serialize);
    }

    public function dirsToScan(): array
    {
        $includePatterns    = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        $includeDirectories = $this->getIncludeDirectories();
        if (empty($includeDirectories)) {
            $includeDirectories = [$this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath()];
        } else {
            $includeDirectories = array_map(function ($directory) {
                return ltrim($directory, '/');
            }, $includeDirectories);
        }

        $collection = $this->collectionFactory->create();
        $pathValues = $collection->getColumnValues('path');

        $returnFiles = [];
        foreach ($includeDirectories as $directory) {
            if (!$this->checkDirectoryReadable($directory)

            ) {
                continue;
            }

            $files = $this->driverFile->readDirectoryRecursively($directory);
            foreach ($files as $file) {
                if (!$this->checkExcludeDirectory($file)) {
                    continue;
                }

                $extensionPath = explode('/', mime_content_type($file))[1];

                if (!in_array($file, $pathValues, true)
                    && ($extensionPath && in_array($extensionPath, $includePatterns, true))
                ) {
                    $returnFiles[] = $file;
                }

                if (count($returnFiles) >= 500) {
                    return $returnFiles;
                }
            }
        }

        return $returnFiles;
    }

    /**
     * Scans Files.
     *
     * @param array $files
     *
     * @return array
     * @throws FileSystemException
     */
    public function scanFiles(array $files): array
    {
        $images             = [];

        foreach (array_chunk($files, 100) as $fileChunk) {
            foreach ($fileChunk as $file) {
                if (!$this->checkExcludeDirectory($file)) {
                    continue;
                }

                $extensionPath = explode('/', mime_content_type($file))[1];

                if (!array_key_exists($file, $images)) {
                    $fileSize = $this->driverFile->stat($file)['size'];
                    if ($fileSize === 0) {
                        continue;
                    }

                    if ($this->isTransparentImage($file, $extensionPath)) {
                        $status  = Status::SKIPPED;
                        $message = __('Skipped because it is a transparent image.');
                    } elseif ($fileSize > 5000000) {
                        $status  = Status::SKIPPED;
                        $message = __('Uploaded file must be below 5MB.');
                    } else {
                        $status  = Status::PENDING;
                        $message = '';
                    }

                    $images[$file] = [
                        'path'        => $file,
                        'status'      => $status,
                        'origin_size' => $fileSize,
                        'message'     => $message,
                    ];
                }
            }
        }

        return array_values($images);
    }

    /**
     * Check Exclude Directory.
     *
     * @param string $file
     *
     * @return bool
     * @throws FileSystemException
     */
    protected function checkExcludeDirectory(string $file): bool
    {
        if (!$this->driverFile->isFile($file)) {
            return false;
        }

        $excludeDirectories = $this->getExcludeDirectories();
        foreach ($excludeDirectories as $excludeDirectory) {
            if (str_contains($file, $excludeDirectory)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks Directory Readable.
     *
     * @param string $directory
     *
     * @return bool
     * @throws FileSystemException
     */
    protected function checkDirectoryReadable(string $directory): bool
    {
        return $this->driverFile->isExists($directory) && $this->driverFile->isReadable($directory);
    }

    /**
     * @param string $path
     *
     * @return mixed
     */
    public function getPathInfo(string $path): mixed
    {
        return $this->ioFile->getPathInfo($path);
    }

    /**
     * @param string $file
     * @param string $extensionPath
     *
     * @return bool
     */
    protected function isTransparentImage(string $file, string $extensionPath): bool
    {
        return $extensionPath === 'png'
            && $this->skipTransparentImage()
            && imagecolortransparent(imagecreatefrompng($file)) >= 0;
    }

    /**
     * Is skip Transparent Image.
     *
     * @return mixed
     */
    public function skipTransparentImage(): mixed
    {
        return $this->getOptimizeOptions('skip_transparent_img');
    }

    /**
     * Optimize Image.
     *
     * @param string $path
     *
     * @return array|mixed
     */
    public function optimizeImage(string $path): mixed
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath() . $path;
        $result = [];
        if (!$this->fileExists($path)) {
            $result = [
                'error'      => true,
                'error_long' => __('file %1 does not exist', $path),
            ];

            return $result;
        }

        try {
            $optimizerChain = $this->optimizer->create();
            $fileSizeBefore = $this->driverFile->stat($path)['size'];

            if ($this->isBackupEnabled()) {
                $this->backupImage($path);
            }

            $optimizerChain->optimize($path);
            $fileSizeAfter = $this->driverFile->stat($path)['size'];

            $percentChange = abs(($fileSizeAfter - $fileSizeBefore) / $fileSizeBefore) * 100;

            $result['src_size']  = $fileSizeBefore;
            $result['dest_size'] = $fileSizeAfter;
            $result['percent']   = $percentChange;
        } catch (Exception $e) {
            $result['error']      = true;
            $result['error_long'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return $this->driverFile->isExists($path);
        //return $this->ioFile->fileExists($path);
    }

    /**
     * @param $url
     * @param $path
     *
     * @return bool|int
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function saveImage($url, $path): bool|int
    {
        if ($this->isBackupEnabled()) {
            $this->backupImage($path);
        }
        if ($this->getOptimizeOptions('force_permission')) {
            $this->driverFile->deleteFile($path);
            $result = $this->ioFile->write(
                $path,
                $this->ioFile->read($url),
                octdec($this->getOptimizeOptions('select_permission'))
            );
        } else {
            $result = $this->ioFile->write(
                $path,
                $this->ioFile->read($url)
            );
        }

        return $result;
    }

    /**
     * Handle image backup process
     *
     * @param $path
     */
    public function backupImage($path)
    {
        $pathInfo = $this->getPathInfo($path);
        $folder   = $this->dir->getPath('var') . '/backup_image/' . $pathInfo['dirname'];
        try {
            $this->ioFile->checkAndCreateFolder($folder, 0775);
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
        if (!$this->fileExists($this->dir->getPath('var') . '/backup_image/' . $path)) {
            $this->ioFile->write($this->dir->getPath('var') .'/backup_image/' . $path, $path, 0664);
        }
    }

    /**
     * Handle image rollback process
     *
     * @param $path
     *
     * @return bool|int
     * @throws LocalizedException
     */
    public function restoreImage($path): bool|int
    {
        if (!$this->fileExists($this->dir->getPath('var') .'/backup_image/' . $path)) {
            throw new LocalizedException(__('Image %1 has not been backed up.', $path));
        }

        return $this->ioFile->write(
            $this->dir->getRoot() . "/" . $path,
            $this->dir->getPath('var') . '/backup_image/' . $path);
    }
}
