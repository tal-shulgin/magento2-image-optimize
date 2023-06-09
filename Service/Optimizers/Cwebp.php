<?php

namespace Mageit\ImageOptimize\Service\Optimizers;

use Mageit\ImageOptimize\Service\Image;

class Cwebp extends BaseOptimizer
{
    public string $binaryName = 'cwebp';

    public function canHandle(Image $image): bool
    {
        return $image->mime() === 'image/webp';
    }

    public function getCommand(): string
    {
        $optionString = implode(' ', $this->options);

        return "\"{$this->binaryPath}{$this->binaryName}\" {$optionString}"
            .' '.escapeshellarg($this->imagePath)
            .' -o '.escapeshellarg($this->imagePath);
    }
}
