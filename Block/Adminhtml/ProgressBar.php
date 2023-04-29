<?php

namespace Mageit\ImageOptimize\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Mageit\ImageOptimize\Helper\Data;
use Mageit\ImageOptimize\Model\Config\Source\Status;
use Mageit\ImageOptimize\Model\ResourceModel\Image\Collection;
use Mageit\ImageOptimize\Model\ResourceModel\Image\CollectionFactory;

class ProgressBar extends Template
{
    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * ProgressBar constructor.
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Data $helperData,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helperData        = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->helperData->isEnabled();
    }

    /**
     * @return Collection
     */
    public function getImagePendingCollection(): Collection
    {
        return $this->getImageCollection()->addFieldToFilter('status', Status::PENDING);
    }

    /**
     * @return Collection
     */
    public function getImageCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    /**
     * @param $status
     *
     * @return string
     */
    public function getBarContent($status): string
    {
        if ($this->getTotalByStatus($status) === 0) {
            return '';
        }

        return $this->getWidthByStatus($status) . ' '
            . $status . ' (' . $this->getTotalByStatus($status)
            . '/' . $this->getTotalImage() . ')';
    }

    /**
     * @param $status
     *
     * @return int
     */
    public function getTotalByStatus($status): int
    {
        $collection = $this->getImageCollection();
        $collection->addFieldToFilter('status', $status);

        return $collection->getSize();
    }

    /**
     * @param $status
     *
     * @return string
     */
    public function getWidthByStatus($status): string
    {
        if ($this->getTotalImage() === 0 || $this->getTotalByStatus($status) === 0) {
            return '0%';
        }
        $width = $this->getTotalByStatus($status) / $this->getTotalImage();

        return round($width * 100, 3) . '%';
    }

    /**
     * @return int
     */
    public function getTotalImage(): int
    {
        return $this->getImageCollection()->getSize();
    }

    /**
     * Get url for optimize image
     *
     * @return string
     */
    public function getOptimizeUrl(): string
    {
        return $this->getUrl('*/*/optimize');
    }

    /**
     * @return string
     */
    public function getImagePendingCollectionJsonEncode(): string
    {
        return $this->toJson($this->getImagePendingCollection()->getItems());
    }
}
