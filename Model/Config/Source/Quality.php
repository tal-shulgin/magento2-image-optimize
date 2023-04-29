<?php

namespace Mageit\ImageOptimize\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Quality implements OptionSourceInterface
{
    const CUSTOM   = 'custom';
    const LOSSLESS = 'lossless';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::CUSTOM, 'label' => __('Custom')],
            ['value' => self::LOSSLESS, 'label' => __('Lossless')]
        ];
    }
}
