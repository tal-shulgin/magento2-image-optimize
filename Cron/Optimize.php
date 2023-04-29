<?php

namespace Mageit\ImageOptimize\Cron;

use Exception;
use Mageit\ImageOptimize\Helper\Data;
use Mageit\ImageOptimize\Model\Config\Source\Status;
use Mageit\ImageOptimize\Model\ResourceModel\Image as ResourceImage;
use Mageit\ImageOptimize\Model\ResourceModel\Image\CollectionFactory;
use Psr\Log\LoggerInterface;

class Optimize
{
    /**
     * Optimize constructor.
     *
     * @param Data $helperData
     * @param ResourceImage $resourceModel
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected Data $helperData,
        protected ResourceImage $resourceModel,
        protected CollectionFactory $collectionFactory,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * @return $this
     */
    public function execute(): static
    {
        if (!$this->helperData->isEnabled() || !$this->helperData->getCronJobConfig('enabled_optimize')) {
            return $this;
        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', Status::PENDING);
        $collection->setPageSize($this->helperData->getCronJobConfig('batch_size'));

        try {
            foreach ($collection as $image) {
                $result = $this->helperData->optimizeImage($image->getData('path'));
                $data   = [
                    'optimize_size' => isset($result['error']) ? null : $result['dest_size'],
                    'percent'       => isset($result['error']) ? null : $result['percent'],
                    'status'        => isset($result['error']) ? Status::ERROR : Status::SUCCESS,
                    'message'       => $result['error_long'] ?? ''
                ];
                $image->addData($data);
                $this->resourceModel->save($image);
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $this;
    }
}
