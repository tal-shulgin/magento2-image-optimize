<?php

namespace Mageit\ImageOptimize\Model\Config\Backend\Cron;

use Exception;
use Magento\Framework\Registry;
use Magento\Framework\Model\Context;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;

class Schedule extends Value
{
    /**
     * Cron optimize path
     */
    public const CRON_OPTIMIZE_PATH = 'crontab/default/jobs/mageit_imageoptimizer_cronjob_optimize/schedule/cron_expr';

    public const CRON_OPTIMIZE_MODEL_PATH = 'crontab/default/jobs/mageit_imageoptimizer_cronjob_optimize/run/model';

    /**
     * Cron scan path
     */
    public const CRON_SCAN_PATH = 'crontab/default/jobs/mageit_imageoptimizer_cronjob_scan/schedule/cron_expr';

    public const CRON_SCAN_MODEL_PATH = 'crontab/default/jobs/mageit_imageoptimizer_cronjob_scan/run/model';

    protected string $runModelPath = "";

    /**
     * @var ValueFactory
     */
    protected ValueFactory $configValueFactory;

    /**
     * Schedule constructor.
     *
     * @param Context               $context
     * @param Registry              $registry
     * @param ScopeConfigInterface  $config
     * @param TypeListInterface     $cacheTypeList
     * @param ValueFactory          $configValueFactory
     * @param ManagerInterface      $messageManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param string                $runModelPath
     * @param array                 $data
     */
    public function __construct(
        Context                    $context,
        Registry                   $registry,
        ScopeConfigInterface       $config,
        TypeListInterface          $cacheTypeList,
        ValueFactory               $configValueFactory,
        ManagerInterface           $messageManager,
        ?AbstractResource          $resource = null,
        ?AbstractDb                $resourceCollection = null,
        string                     $runModelPath = '',
        array                      $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );

        $this->runModelPath = $runModelPath;
        $this->configValueFactory = $configValueFactory;
    }

    /**
     * @return Value
     * @throws Exception
     */
    public function afterSave(): Value
    {
        $enableScan       = $this->getData('groups/cron_job/fields/enabled_scan/value');
        $scanSchedule     = $this->getData('groups/cron_job/fields/scan_schedule/value');

        $enableOptimize   = $this->getData('groups/cron_job/fields/enabled_optimize/value');
        $optimizeSchedule = $this->getData('groups/cron_job/fields/optimize_schedule/value');

        if ($enableScan) {
            try {
                $this->configValueFactory->create()->load(
                    self::CRON_SCAN_PATH,
                    'path'
                )->setValue(
                    $scanSchedule
                )->setPath(
                    self::CRON_SCAN_PATH
                )->save();

                $this->configValueFactory->create()->load(
                    self::CRON_SCAN_MODEL_PATH,
                    'path'
                )->setValue(
                    $this->runModelPath
                )->setPath(
                    self::CRON_SCAN_MODEL_PATH
                )->save();
            } catch (Exception $e) {
                throw new \Exception(__('We can\'t save the cron expression. %1', $e->getMessage()));
            }
        }

        if ($enableOptimize) {
            try {
                $this->configValueFactory->create()->load(
                    self::CRON_OPTIMIZE_PATH,
                    'path'
                )->setValue(
                    $optimizeSchedule
                )->setPath(
                    self::CRON_OPTIMIZE_PATH
                )->save();

                $this->configValueFactory->create()->load(
                    self::CRON_OPTIMIZE_MODEL_PATH,
                    'path'
                )->setValue(
                    $this->runModelPath
                )->setPath(
                    self::CRON_OPTIMIZE_MODEL_PATH
                )->save();
            } catch (Exception $e) {
                throw new \Exception(__('We can\'t save the cron expression. %1', $e->getMessage()));
            }
        }

        return parent::afterSave();
    }
}
