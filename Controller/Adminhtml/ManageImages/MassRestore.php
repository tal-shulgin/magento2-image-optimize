<?php

namespace Mageit\ImageOptimize\Controller\Adminhtml\ManageImages;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageit\ImageOptimize\Controller\Adminhtml\Image;
use Mageit\ImageOptimize\Model\Config\Source\Status;

class MassRestore extends Image
{
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->helperData->isEnabled()) {
            return $this->isDisable($resultRedirect);
        }

        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/');
        }
        $updated = 0;
        foreach ($collection as $image) {
            try {
                $result = $this->helperData->restoreImage($image->getData('path'));
                if ($result) {
                    $image->addData([
                        'status'        => Status::SKIPPED,
                        'optimize_size' => null,
                        'percent'       => null,
                        'message'       => ''
                    ]);
                    $image->save();
                    $updated++;
                } else {
                    $image->addData([
                        'status'  => Status::ERROR,
                        'message' => __('The file %1 is not writable', $image->getData('path'))
                    ]);
                    $image->save();
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while restore for %1.', $image->getData('path'))
                );
                $this->logger->critical($e->getMessage());
            }
        }

        if ($updated) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $updated));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
