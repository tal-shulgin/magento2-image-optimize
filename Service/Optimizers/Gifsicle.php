<?php

namespace Mageit\ImageOptimize\Service\Optimizers;

use Mageit\ImageOptimize\Service\Image;

class Gifsicle extends BaseOptimizer
{
    public string $binaryName = 'gifsicle';

    public function canHandle(Image $image): bool
    {
        return $image->mime() === 'image/gif';
    }

    public function getCommand(): string
    {
        $optionString = implode(' ', $this->options);

        return "\"{$this->binaryPath}{$this->binaryName}\" {$optionString}"
            . ' -i ' . escapeshellarg($this->imagePath)
            . ' -o ' . escapeshellarg($this->imagePath);
    }
}
