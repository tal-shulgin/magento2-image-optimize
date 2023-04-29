<?php

namespace Mageit\ImageOptimize\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Mageit\ImageOptimize\Model\ResourceModel\Image as ImageResourceModel;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Image extends AbstractModel implements IdentityInterface
{
    /**
     * Cache tag
     */
    public const CACHE_TAG = 'mageit_image_optimizer';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageit_image_optimizer';

    /**
     * @return array|string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /** @SuppressWarnings(PHPMD.CamelCaseMethodName) */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ImageResourceModel::class);
    }
}
