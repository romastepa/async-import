<?php
declare(strict_types=1);

namespace Magento\AsynchronousImportWebsite\Model\Import\ConvertingRule;

use Magento\AsynchronousImportApi\Api\Data\ConvertingRuleInterface;
use Magento\AsynchronousImportApi\Api\Data\ImportDataInterface;
use Magento\AsynchronousImportApi\Model\ConvertingRuleProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Converts WebsiteCode to Website Id
 */
class WebsiteCodeToId implements ConvertingRuleProcessorInterface
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
    protected $websiteCodeToId;

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
     * Takes apply_to columns and converts values websiteCode to WebsiteId.
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
        $this->initWebsites();
        $rows = $importData->getData();

        foreach ($applyTo as $column) {
            foreach ($rows as &$row) {
                $row[$column] = $this->getWebsiteId($row[$column], $column);
            }
        }

        return $importData->{ImportDataInterface::DATA} = $rows;
    }

    /**
     * Gets WebsiteId by WebsiteCode
     *
     * @param $websiteCode
     * @param $column
     *
     * @return int
     */
    protected function getWebsiteId($websiteCode, $column)
    {
        if (!isset($this->websiteCodeToId[$websiteCode])) {
            throw new NotFoundException(__(
                'The converting rule apply_to cannot be applied to the column: "%column". WebsiteCode "%code%" not exists', [
                    'column' => $column,
                    'code' => $websiteCode
            ]));
        }

        return $this->storeManager[$websiteCode];
    }

    /**
     * Initialize websites hash.
     *
     * @return $this
     */
    protected function initWebsites()
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            $this->websiteCodeToId[$website->getCode()] = $website->getId();
        }
        return $this;
    }
}
