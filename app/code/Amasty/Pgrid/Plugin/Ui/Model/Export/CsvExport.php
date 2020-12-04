<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


declare(strict_types=1);

namespace Amasty\Pgrid\Plugin\Ui\Model\Export;

class CsvExport extends AbstractExport
{
    /**
     * @var string
     */
    protected $exportType = 'csv';

    /**
     * @param \Magento\Ui\Model\Export\ConvertToCsv $subject
     * @param \Closure $proceed
     * @return array|mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetCsvFile(
        \Magento\Ui\Model\Export\ConvertToCsv $subject,
        \Closure $proceed
    ) {
        return $this->checkNamespace() ? $this->getCsvFile() : $proceed();
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();
        $file = 'export/'. $component->getName() . $this->random->getRandomString(16) . '.csv';
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $selectedProducts = $this->request->getParam(\Magento\Ui\Component\MassAction\Filter::SELECTED_PARAM, []);

        if (count($selectedProducts) > 0) {
            $collection = $component->getContext()->getDataProvider()->getCollection();
            $collection->setPageSize(count($selectedProducts));
            $collection->getSelect()->order(
                new \Zend_Db_Expr(sprintf('FIELD(e.entity_id,%s)', implode(',', $selectedProducts)))
            );
        }

        $dataSource = $component->getContext()->getDataProvider()->getData();
        $fieldMapping = $this->getSortedColumnFieldMapping($component);
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->getHeaders($component, $fieldMapping));

        foreach ($dataSource['items'] as $item) {
            $this->metadataProvider->convertDate($item, $component->getName());
            $stream->writeCsv($this->getRowData($item, $fieldMapping));
        }

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }
}
