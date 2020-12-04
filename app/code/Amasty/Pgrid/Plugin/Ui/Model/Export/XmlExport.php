<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


declare(strict_types=1);

namespace Amasty\Pgrid\Plugin\Ui\Model\Export;

use Magento\Framework\Convert\Excel;

class XmlExport extends AbstractExport
{
    /**
     * @var string
     */
    protected $exportType = 'xml';

    /**
     * @param \Magento\Ui\Model\Export\ConvertToXml $subject
     * @param \Closure $proceed
     * @return array|mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetXmlFile(
        \Magento\Ui\Model\Export\ConvertToXml $subject,
        \Closure $proceed
    ) {
        return $this->checkNamespace() ? $this->getXmlFile() : $proceed();
    }

    /**
     * Returns Excel XML file
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getXmlFile()
    {
        $component = $this->filter->getComponent();
        $file = 'export/'. $component->getName() . $this->random->getRandomString(16) . '.xml';
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
        $searchResultIterator = $this->iteratorFactory->create(['items' => $dataSource['items']]);
        /** @var Excel $excel */
        $excel = $this->excelFactory->create([
            'iterator' => $searchResultIterator,
            'rowCallback'=> [$this, 'getRowXmlData'],
        ]);
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $excel->setDataHeader($this->getHeaders($component, $fieldMapping));
        $excel->write($stream, $component->getName() . '.xml');
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }
}
