<?php

namespace Mageit\ImageOptimize\Model\ResourceModel\Image;

use Mageit\ImageOptimize\Model\Image;
use Mageit\ImageOptimize\Model\ResourceModel\Image as ImageResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/** @SuppressWarnings(PHPMD.CamelCasePropertyName) */
class Collection extends AbstractCollection
{
    /**
     * @var string
     * @SuppressWarnings(PHPMD.CamelCasePropertyName)
     */
    protected $_idFieldName = 'image_id';


    /** @SuppressWarnings(PHPMD.CamelCaseMethodName) */
    protected function _construct()
    {
        $this->_init(Image::class, ImageResourceModel::class);
    }
}
