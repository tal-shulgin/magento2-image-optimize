<?php

namespace Mageit\ImageOptimize\Model\Config\Backend;

use Magento\Framework\Exception\LocalizedException;

class Paths extends \Magento\Config\Model\Config\Backend\Serialized
{

    /**
     * Validate and prepare data before saving config value.
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave(): static
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
        }
        $this->setValue($value);

        $pattern = '/^[\p{L}\p{N}_,;:!&#\+\*\$\?\|\'\.\-\ \/]+$/iu';


        foreach ($value as $path) {
            $validator = preg_match($pattern, $path['path']);

            if (!$validator) {
                $message = __('Please correct Paths: "%1".', $path['path']);
                throw new LocalizedException($message);
            }
        }


        return parent::beforeSave();
    }
}
