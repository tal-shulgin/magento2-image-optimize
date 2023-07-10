<?php

namespace Mageit\ImageOptimize\Model;

use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;
use Mageit\ImageOptimize\Helper\Config;
use Mageit\ImageOptimize\Service\OptimizerChain;
use Mageit\ImageOptimize\Service\Optimizers\Cwebp;
use Mageit\ImageOptimize\Service\Optimizers\Gifsicle;
use Mageit\ImageOptimize\Service\Optimizers\Jpegoptim;
use Mageit\ImageOptimize\Service\Optimizers\Optipng;
use Mageit\ImageOptimize\Service\Optimizers\Pngquant;
use Mageit\ImageOptimize\Service\Optimizers\Svgo;
use Magento\Framework\Filesystem\DirectoryList;

class Optimizer
{
    public function __construct(
        protected Config $configHelper,
        protected DirectoryList $directoryList,
        protected LoggerInterface $logger
    ) {
    }

    public function getOptimizeConfig(string $type) :array
    {
        $options = $this->configHelper->getOptimizeOptions($type . "_options");
        if (!empty($options)) {
            return explode(" ", $options);
        }

        return match ($type) {
            "gif" => [
                '-b',
                '-O3',
            ],
            "jpg" => [
                '--max=85',
                '--strip-all',
                '--all-progressive',
            ],
            "png" => [
                '-i0',
                '-o2',
                '-quiet',
            ],
            default => [],
        };
    }

    /**
     * Gets optimizer custom path if sets, else use global installed optimizer.
     * @param string $type
     *
     * @return mixed
     * @throws FileSystemException
     */
    public function getOptimizeCustomPath(string $type): mixed
    {
        if ($this->configHelper->isUseCustomPaths() && $this->configHelper->getOptimizeOptions($type ."_path")) {
            return $this->configHelper->getOptimizeOptions($type ."_path");
        }

        if ($this->configHelper->isUseCustomPaths()) {
            return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) .
                '/mageit/bin/' .
                $this->configHelper->getOptimizeOptions($type);
        }

        return '';
    }

    public function create(): OptimizerChain
    {
        $jpg =  new Jpegoptim([
            '--max=85',
           '--strip-all',
            '--all-progressive',
        ]);
        $jpg->setBinaryPath($this->getOptimizeCustomPath('jpg'));

        $png = new Pngquant([
            '--quality=85',
            '--force',
            '--skip-if-larger',
        ]);
        $png->setBinaryPath($this->getOptimizeCustomPath('png'));

        $gif = new Gifsicle([
            '-b',
            '-O3',
        ]);
        $gif->setBinaryPath($this->getOptimizeCustomPath('gif'));

        return (new OptimizerChain($this->logger))
            ->addOptimizer($gif)
            ->addOptimizer($jpg)
            ->addOptimizer($png)
            ->addOptimizer(new Optipng([
                '-i0',
                '-o2',
                '-quiet',
            ]))
            ->addOptimizer(new Cwebp([
                '-m 6',
                '-pass 10',
                '-mt',
            ]));
    }
}
