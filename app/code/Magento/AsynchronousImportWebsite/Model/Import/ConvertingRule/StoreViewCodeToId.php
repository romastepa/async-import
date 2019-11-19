<?php
declare(strict_types=1);

namespace Magento\AsynchronousImportWebsite\Model\Import\ConvertingRule;

use Magento\AsynchronousImportDataConvertingApi\Api\Data\ConvertingRuleInterface;
use Magento\AsynchronousImportDataConvertingApi\Model\ApplyConvertingRuleStrategyInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Converts StoreViewCode to Store Id
 */
class StoreViewCodeToId implements ApplyConvertingRuleStrategyInterface
{
    /**
     * Store manager instance.
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $storeCodeToId;

    /**
     * StoreViewCodeToId constructor.
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Executes converting rule
     *
     * Takes apply_to columns and converts values StoreViewCode to StoreId.
     *
     * @param array $importData
     * @param ConvertingRuleInterface $convertingRule
     * @return array
     */
    public function execute(
        array $importData,
        ConvertingRuleInterface $convertingRule
    ): array {
        $applyTo = $convertingRule->getApplyTo();

        $this->initStores();

        foreach ($importData as &$row) {
            foreach ($applyTo as $columnName) {
                if (isset($row[$columnName])) {
                    $row[$columnName] = $this->getStoreId($row[$columnName], $columnName);
                }
            }
        }
        unset($row);

        return $importData;
    }

    /**
     * Gets StoreID by StoreCode
     *
     * @param $storeCode
     * @param $column
     *
     * @return int
     */
    protected function getStoreId($storeCode, $column)
    {
        if (!isset($this->storeCodeToId[$storeCode])) {
            throw new NotFoundException(__(
                'The converting rule apply_to cannot be applied to the column: "%column". Store code "%code%" not exists',
                [
                    'column' => $column,
                    'code' => $storeCode
                ]
            ));
        }

        return $this->storeManager[$storeCode];
    }

    /**
     * Initialize stores hash.
     *
     * @return $this
     */
    protected function initStores()
    {
        foreach ($this->storeManager->getStores(true) as $store) {
            $this->storeCodeToId[$store->getCode()] = $store->getId();
        }
        return $this;
    }
}
