<?php

namespace Mageit\ImageOptimize\Service\Optimizers;

use Mageit\ImageOptimize\Service\Image;

class Jpegoptim extends BaseOptimizer
{
    public string $binaryName = 'jpegoptim';

    public function canHandle(Image $image): bool
    {
        return $image->mime() === 'image/jpeg';
    }
}
