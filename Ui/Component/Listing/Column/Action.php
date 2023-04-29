<?php

namespace Mageit\ImageOptimize\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Action extends Column
{
    /** Url path */
    public const URL_PATH_OPTIMIZE = 'mageitimageoptimizer/manageimages/optimize';
    public const URL_PATH_RESTORE  = 'mageitimageoptimizer/manageimages/restore';
    public const URL_PATH_REQUEUE  = 'mageitimageoptimizer/manageimages/requeue';
    public const URL_PATH_SKIP     = 'mageitimageoptimizer/manageimages/skip';
    public const URL_PATH_DELETE   = 'mageitimageoptimizer/manageimages/delete';

    protected UrlInterface $urlBuilder;

    /**
     * Action constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface       $context,
        UiComponentFactory     $uiComponentFactory,
        UrlInterface           $urlBuilder,
        array                  $components = [],
        array                  $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['image_id'])) {
                    $item[$name]['optimize'] = [
                        'href'  => $this->urlBuilder->getUrl(
                            self::URL_PATH_OPTIMIZE,
                            ['image_id' => $item['image_id']]
                        ),
                        'label' => __('Optimize')
                    ];
                    $item[$name]['restore']  = [
                        'href'  => $this->urlBuilder->getUrl(
                            self::URL_PATH_RESTORE,
                            ['image_id' => $item['image_id']]
                        ),
                        'label' => __('Restore')
                    ];
                    $item[$name]['requeue']  = [
                        'href'  => $this->urlBuilder->getUrl(
                            self::URL_PATH_REQUEUE,
                            ['image_id' => $item['image_id']]
                        ),
                        'label' => __('Requeue')
                    ];
                    $item[$name]['delete']   = [
                        'href'    => $this->urlBuilder->getUrl(
                            self::URL_PATH_DELETE,
                            ['image_id' => $item['image_id']]
                        ),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title'         => __('Delete'),
                            'message'       => __('Are you sure you want to delete a record?'),
                            '__disableTmpl' => true,
                        ],
                        'post'    => true,
                    ];
                    $item[$name]['skip']     = [
                        'href'  => $this->urlBuilder->getUrl(
                            self::URL_PATH_SKIP,
                            ['image_id' => $item['image_id']]
                        ),
                        'label' => __('Skip')
                    ];
                }
            }
        }

        return $dataSource;
    }
}
