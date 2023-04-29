<?php

namespace Mageit\ImageOptimize\Controller\Adminhtml\ManageImages;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Mageit\ImageOptimize\Controller\Adminhtml\Image;
use Mageit\ImageOptimize\Helper\Data;
use Mageit\ImageOptimize\Model\Config\Source\Status;

class Optimize extends Image
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $isAjax = $this->getRequest()->getParam('isAjax');
        if (!$this->helperData->isEnabled() && !$isAjax) {
            return $this->isDisable($resultRedirect);
        }

        $imageId = $this->getRequest()->getParam('image_id');
        try {
            $model = $this->imageFactory->create();
            if ($imageId) {
                $this->resourceModel->load($model, $imageId);
                if ($imageId !== $model->getId()) {
                    return $this->checkStatus($model, $resultRedirect, $isAjax);
                }
                $status = $model->getData('status');
                if ($status === Status::SUCCESS) {
                    return $this->checkStatus($model, $resultRedirect, $isAjax);
                }

                if ($status === Status::SKIPPED) {
                    return $this->checkStatus($model, $resultRedirect, $isAjax);
                }
            }
            $result = $this->helperData->optimizeImage($model->getData('path'));
            $this->saveImage($model, $result);
            if ($isAjax) {
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                if (isset($result['error'])) {
                    $resultJson->setData([
                        'status' => __('Error'),
                        'path'   => $model->getData('path')
                    ]);
                    return $resultJson;
                }

                $resultJson->setData([
                    'status' => __('Success'),
                    'path'   => $model->getData('path')
                ]);
                return $resultJson;
            }
            $this->getMessageContent($result);
        } catch (Exception $e) {
            if ($isAjax) {
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData([
                    'status' => __('Error'),
                    'path'   => $model->getData('path')
                ]);
                return $resultJson;
            }
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param \Mageit\ImageOptimize\Model\Image $model
     * @param array                             $result
     *
     * @return \Mageit\ImageOptimize\Model\Image
     * @throws AlreadyExistsException
     */
    protected function saveImage(\Mageit\ImageOptimize\Model\Image $model, array $result): \Mageit\ImageOptimize\Model\Image
    {
        $data = [
            'optimize_size' => isset($result['error']) ? $model->getData('optimize_size') : $result['dest_size'],
            'percent'       => isset($result['error']) ? null : $result['percent'],
            'status'        => isset($result['error']) ? Status::ERROR : Status::SUCCESS,
            'message'       => $result['error_long'] ?? ''
        ];
        $model->addData($data);
        $this->resourceModel->save($model);

        return $model;
    }

    /**
     * @param array $result
     */
    protected function getMessageContent(array $result)
    {
        if (isset($result['error'])) {
            $this->messageManager->addErrorMessage(__($result['error_long']));
        } else {
            $this->messageManager->addSuccessMessage(__('Image(s) have been optimized successfully.'));
        }
    }

    /**
     * @param $model
     * @param $resultRedirect
     * @param bool $isAjax
     *
     * @return mixed
     */
    protected function checkStatus($model, $resultRedirect, bool $isAjax = false): mixed
    {
        $status = $model->getData('status');
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if ($status === Status::SUCCESS) {
            if ($isAjax) {
                $resultJson->setData([
                    'status' => __('Already optimized, please requeue to optimize again'),
                    'path'   => $model->getData('path')
                ]);
                return $resultJson;
            }
            $this->messageManager->addErrorMessage(__('The image(s) had already been optimized previously'));

            return $resultRedirect->setPath('*/*/');
        }

        if ($status === Status::SKIPPED) {
            if ($isAjax) {
                $resultJson->setData([
                    'status' => __('Skipped'),
                    'path'   => $model->getData('path')
                ]);
                return $resultJson;
            }
            $this->messageManager->addErrorMessage(__('The image(s) are skipped.'));

            return $resultRedirect->setPath('*/*/');
        }

        if ($isAjax) {
            $resultJson->setData([
                'status' => __('Error'),
                'path'   => $model->getData('path')
            ]);
            return $resultJson;
        }

        $this->messageManager->addErrorMessage(__('The wrong image is specified.'));
        return $resultRedirect->setPath('*/*/');
    }
}
