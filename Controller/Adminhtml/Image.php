<?php

namespace Mageit\ImageOptimize\Controller\Adminhtml;

use Psr\Log\LoggerInterface;
use Magento\Backend\App\Action;
use Mageit\ImageOptimize\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Ui\Component\MassAction\Filter;
use Mageit\ImageOptimize\Model\ImageFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\Redirect;
use Mageit\ImageOptimize\Model\ResourceModel\Image as ResourceImage;
use Mageit\ImageOptimize\Model\ResourceModel\Image\CollectionFactory;

abstract class Image extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mageit_ImageOptimize::grid';
    
    /**
     * Image constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ImageFactory $imageFactory
     * @param ResourceImage $resourceModel
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param Data $helperData
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected Context $context,
        protected PageFactory $resultPageFactory,
        protected ImageFactory $imageFactory,
        protected ResourceImage $resourceModel,
        protected CollectionFactory $collectionFactory,
        protected Filter $filter,
        protected Data $helperData,
        protected LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * @return Page
     */
    protected function initPage(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageit_ImageOptimize::grid')
            ->addBreadcrumb(__('Image Optimizer'), __('Image Optimizer'))
            ->addBreadcrumb(__('Manage Images'), __('Manage Images'));

        return $resultPage;
    }

    /**
     * @param Redirect $resultRedirect
     *
     * @return Redirect
     */
    protected function isDisable(Redirect $resultRedirect): Redirect
    {
        $this->messageManager->addErrorMessage(__('The module has been disabled.'));

        return $resultRedirect->setPath('*/*/');
    }
}
