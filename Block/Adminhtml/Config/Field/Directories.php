<?php

namespace Mageit\ImageOptimize\Block\Adminhtml\Config\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Directories extends AbstractFieldArray
{
    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('path', ['label' => __('Path'), 'renderer' => false, 'class' => 'required-entry']);

        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add');
    }
}
