<?php
declare(strict_types=1);

namespace Magento\AsynchronousImportWebsite\Model\Import\ConvertingRule;

use Magento\AsynchronousImportApi\Api\Data\ConvertingRuleInterface;
use Magento\AsynchronousImportApi\Api\Data\ImportDataInterface;
use Magento\AsynchronousImportApi\Model\ConvertingRuleProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Converts StoreViewCode to Store Id
 */
class StoreViewCodeToId implements ConvertingRuleProcessorInterface
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
     * @param ImportDataInterface $importData
     * @param ConvertingRuleInterface $convertingRule
     * @return ImportDataInterface
     */
    public function execute(
        ImportDataInterface $importData,
        ConvertingRuleInterface $convertingRule
    ): ImportDataInterface
    {
        $applyTo = $convertingRule->getApplyTo() ?? [];
        if ($applyTo === []) {
            return $importData;
        }
        $this->initStores();
        $rows = $importData->getData();

        foreach ($applyTo as $column) {
            foreach ($rows as &$row) {
                $row[$column] = $this->getStoreId($row[$column], $column);
            }
        }

        return $importData->{ImportDataInterface::DATA} = $rows;
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
        $storeCode = mb_strtolower($storeCode);
        if (!isset($this->storeManager[$storeCode])) {
            throw new NotFoundException(__(
                'The converting rule apply_to cannot be applied to the column: "%column". Store code "%code%" not exists', [
                    'column' => $column,
                    'code' => $storeCode
            ]));
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
        foreach ($this->storeManager->getStores() as $store) {
            $this->storeCodeToId[$store->getCode()] = $store->getId();
        }
        return $this;
    }
}
