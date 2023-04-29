<?php

namespace Mageit\ImageOptimize\Controller\Adminhtml\ManageImages;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageit\ImageOptimize\Controller\Adminhtml\Image;

class MassDelete extends Image
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
            $delete     = $collection->getSize();
            $collection->walk('delete');
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $delete));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
