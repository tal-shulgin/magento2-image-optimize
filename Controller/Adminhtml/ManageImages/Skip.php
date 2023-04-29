<?php

namespace Mageit\ImageOptimize\Controller\Adminhtml\ManageImages;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageit\ImageOptimize\Controller\Adminhtml\Image;
use Mageit\ImageOptimize\Model\Config\Source\Status;

class Skip extends Image
{
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->helperData->isEnabled()) {
            return $this->isDisable($resultRedirect);
        }

        $model   = $this->imageFactory->create();
        $imageId = $this->getRequest()->getParam('image_id');
        try {
            if ($imageId) {
                $this->resourceModel->load($model, $imageId);
                if ($imageId !== $model->getId()) {
                    $this->messageManager->addErrorMessage(__('The wrong image is specified.'));

                    return $resultRedirect->setPath('*/*/');
                }
            }
            $model->setData('status', Status::SKIPPED);
            $this->resourceModel->save($model);
            $this->messageManager->addSuccessMessage(__('The image has been successfully updated.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
