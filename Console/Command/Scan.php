<?php
declare(strict_types=1);

namespace Mageit\ImageOptimize\Console\Command;

use Exception;
use Magento\Framework\Console\Cli;
use Mageit\ImageOptimize\Helper\Data;
use Mageit\ImageOptimize\Model\ResourceModel\Image as ResourceImage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Scan extends Command
{
    /**
     * Scan constructor.
     *
     * @param Data            $helperData
     * @param ResourceImage   $resourceModel
     * @param LoggerInterface $logger
     * @param string|null     $name
     */
    public function __construct(
        protected Data $helperData,
        protected ResourceImage $resourceModel,
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
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->helperData->isEnabled()) {
            $message = "<error>". __('Command cannot run because the module is disabled.'). "</error>";
            $output->writeln($message);

            return Cli::RETURN_FAILURE;
        }

        try {
            $data = $this->helperData->scanFiles();
            if (empty($data)) {
                $message = "<info>". __('Sorry, no images are found after scan.') . "</info>";
                $output->writeln($message);

                return Cli::RETURN_FAILURE;
            }
            $this->resourceModel->insertImagesData($data);
            $message = "<info>". __('Successful data scanning.') . "</info>";
            $output->writeln($message);

            return Cli::RETURN_SUCCESS;
        } catch (Exception  $e) {$message = "<error>".
                __('Something went wrong while scan images. Please review the error log.').
                "</error>";

            $output->writeln($message);
            $this->logger->critical($e->getMessage());

            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('mageit:imageoptimizer:scan');
        $this->setDescription(__('Image Optimizer scan images.'));
        parent::configure();
    }
}
