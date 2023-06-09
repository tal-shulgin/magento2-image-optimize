<?php

namespace Mageit\ImageOptimize\Cron;

use Exception;
use Mageit\ImageOptimize\Helper\Data;
use Mageit\ImageOptimize\Model\ResourceModel\Image as ResourceImage;
use Psr\Log\LoggerInterface;

class Scan
{
    /**
     * Scan constructor.
     *
     * @param Data $helperData
     * @param ResourceImage $resourceModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected Data $helperData,
        protected ResourceImage $resourceModel,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * @return $this
     */
    public function execute(): static
    {
        if (!$this->helperData->isEnabled() || !$this->helperData->isScanCronEnabled()) {
            return $this;
        }

        try {
            $files = $this->helperData->dirsToScan();
            foreach (array_chunk($files, 500) as $filesChunk) {
                $data = $this->helperData->scanFiles($filesChunk);
                if (empty($data)) {
                    continue;
                }

                $this->resourceModel->insertImagesData($data);
            }
        } catch (Exception  $e) {
            $this->logger->critical($e->getMessage());
        }

        return $this;
    }
}
