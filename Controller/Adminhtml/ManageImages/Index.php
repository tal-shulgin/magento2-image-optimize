<?php

namespace Mageit\ImageOptimize\Controller\Adminhtml\ManageImages;

use Magento\Framework\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageit\ImageOptimize\Controller\Adminhtml\Image;

class Index extends Image
{
    public function execute()
    {
        $resultPage = $this->initPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Images'));

        return $resultPage;
    }
}
