<?php

namespace Mageit\ImageOptimize\Console\Command;

use Exception;
use Magento\Framework\Console\Cli;
use Mageit\ImageOptimize\Helper\Data;
use Mageit\ImageOptimize\Model\Config\Source\Status;
use Mageit\ImageOptimize\Model\ResourceModel\Image as ResourceImage;
use Mageit\ImageOptimize\Model\ResourceModel\Image\Collection as ImageOptimizerCollection;
use Mageit\ImageOptimize\Model\ResourceModel\Image\CollectionFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Optimize extends Command
{
    /**
     * Optimize constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param ResourceImage     $resourceModel
     * @param Data              $helperData
     * @param LoggerInterface   $logger
     * @param string|null       $name
     */
    public function __construct(
       protected CollectionFactory $collectionFactory,
       protected ResourceImage $resourceModel,
       protected Data $helperData,
       protected LoggerInterface $logger,
       protected ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->helperData->isEnabled()) {
            $message = "<error>". __('Command cannot run because the module is disabled.'). "</error>";
            $output->writeln($message);
            return Cli::RETURN_FAILURE;
        }

        $count = 0;
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', Status::PENDING);
        $limit = $this->helperData->getCronJobConfig('batch_size');
        $size  = $collection->getSize();
        if ($limit < $size) {
            $collection->setPageSize($limit);
        } else {
            $limit = $size;
        }

        foreach ($collection as $image) {
            try {
                $result = $this->helperData->optimizeImage($image->getData('path'));
                $data   = [
                    'optimize_size' => isset($result['error']) ? null : $result['dest_size'],
                    'percent'       => isset($result['error']) ? null : $result['percent'],
                    'status'        => $this->getStatus($result),
                    'message'       => $result['error_long'] ?? ''
                ];

                $image->addData($data);

                $this->resourceModel->save($image);
                $count++;
                $percent = round(($count / $limit) * 100, 2) . '%';
                if (isset($result['error'])) {
                    $output->writeln(
                        __('<error>The problem occurred during image optimization %1.</error>', $image->getData('path'))
                    );
                    $this->logger->critical($result['error_long']);
                } else {
                    $output->writeln(
                        "<info>" .
                        __(
                            'Image %1 have been optimized successfully. (%2/%3 %4)',
                            $image->getData('path'),
                            $count,
                            $limit,
                            $percent
                        ) .
                        "</info>"
                    );
                }
            } catch (Exception $e) {
                $output->writeln(
                    "<error>" .
                    __('The problem occurred during image optimization %1.', $image->getData('path')) .
                    "</error>"
                );
                $this->logger->critical($e->getMessage());
            }
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param array $response
     *
     * @return string
     */
    protected function getStatus(array $response): string
    {
        if (isset($result['error'])) {
            return Status::ERROR;
        }
        if ($response['src_size'] === $response['dest_size']) {
            return Status::SKIPPED;
        }

        return Status::SUCCESS;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('mageit:imageoptimizer:optimize');
        $this->setDescription(__('Image Optimizer optimize images.'));

        parent::configure();
    }
}
