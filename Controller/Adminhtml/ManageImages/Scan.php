<?php

namespace Mageit\ImageOptimize\Controller\Adminhtml\ManageImages;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Mageit\ImageOptimize\Controller\Adminhtml\Image;

class Scan extends Image
{
    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->helperData->isEnabled()) {
            return $this->isDisable($resultRedirect);
        }

        try {
            $data = $this->helperData->scanFiles();
            if (empty($data)) {
                $this->messageManager->addErrorMessage(__('Sorry, no images are found after scan.'));

                return $resultRedirect->setPath('*/*/');
            }
            $this->resourceModel->insertImagesData($data);
            $this->messageManager->addSuccessMessage(__('Successful data scanning'));
        } catch (Exception  $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while scan image. Please review the error log.')
            );
            $this->logger->critical($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
