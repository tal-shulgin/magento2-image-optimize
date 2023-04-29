<?php

namespace Mageit\ImageOptimize\Block\Adminhtml;

use Magento\Backend\Block\Widget\Container;

class Image extends Container
{
    /**
     * Prepare button and grid
     *
     * @return Container
     */
    protected function _prepareLayout(): Container
    {
        $addButtonOptimize = [
            'id'           => 'optimize_image',
            'label'        => __('Optimize Images'),
            'class'        => 'primary',
            'button_class' => '',
            'onclick'      => 'setLocation(\'' . $this->getOptimizeUrl() . '\')',
        ];
        $this->buttonList->add('optimize_image', $addButtonOptimize);

        $addButtonScan = [
            'id'           => 'scan_image',
            'label'        => __('Scan Images'),
            'class'        => 'primary',
            'button_class' => '',
            'onclick'      => 'setLocation(\'' . $this->getScanUrl() . '\')',
        ];
        $this->buttonList->add('scan_image', $addButtonScan);

        return parent::_prepareLayout();
    }

    /**
     * Get url for scan image
     *
     * @return string
     */
    public function getScanUrl(): string
    {
        return $this->getUrl('*/*/scan');
    }

    /**
     * Get url for optimize image
     * @return string
     */
    public function getOptimizeUrl(): string
    {
        return $this->getUrl('*/*/optimize');
    }
}
