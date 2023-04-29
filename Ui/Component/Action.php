<?php

namespace Mageit\ImageOptimize\Ui\Component;

use Magento\Ui\Component\AbstractComponent;
use Mageit\ImageOptimize\Helper\Data;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Action extends AbstractComponent
{
    public const NAME = 'action';

    /**
     * Action constructor.
     *
     * @param ContextInterface $context
     * @param Data             $helperData
     * @param array            $components
     * @param array            $data
     * @param null             $actions
     */
    public function __construct(
        ContextInterface $context,
        protected Data $helperData,
        array $components = [],
        array $data = [],
        protected $actions = null
    ) {
        parent::__construct($context, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        if (!$this->helperData->isEnabled()) {
            $this->setData('config', []);
        }

        if (!empty($this->actions)) {
            $this->setData('config', array_replace_recursive(['actions' => $this->actions], $this->getConfiguration()));
        }

        parent::prepare();
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName(): string
    {
        return static::NAME;
    }
}
