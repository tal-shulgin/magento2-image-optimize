<?php

namespace Mageit\ImageOptimize\Service\Optimizers;

use Mageit\ImageOptimize\Service\Image;

class Optipng extends BaseOptimizer
{
    public string $binaryName = 'optipng';

    public function canHandle(Image $image): bool
    {
        return $image->mime() === 'image/png';
    }
}
