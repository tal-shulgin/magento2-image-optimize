<?php

namespace Mageit\ImageOptimize\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    const PENDING = 'pending';
    const ERROR   = 'error';
    const SUCCESS = 'success';
    const SKIPPED = 'skipped';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::PENDING, 'label' => __('Pending')],
            ['value' => self::ERROR, 'label' => __('Error')],
            ['value' => self::SUCCESS, 'label' => __('Success')],
            ['value' => self::SKIPPED, 'label' => __('Skipped')]
        ];
    }
}
