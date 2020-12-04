<?php
/** Block handling the price per destination and weight in configuration
 */

namespace LaPoste\Colissimo\Block\System\Config;

class ArraySetupMethodPrice extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    protected $_weightRenderer;
    protected $areaRenderer;
    protected $dataHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \LaPoste\Colissimo\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Structure of the field
     */
    protected function _prepareToRender()
    {
        $this->addColumn('area', ['label' => __('Area'), 'renderer' => $this->getAreaRender()]);
        $this->addColumn('weight', ['label' => __('Cart weight/price starting from')]);
        $this->addColumn('price', ['label' => __('Price')]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('New slice');
    }

    protected function getAreaRender()
    {
        if (!$this->areaRenderer) {
            $this->areaRenderer = $this->getLayout()->createBlock(
                '\LaPoste\Colissimo\Block\System\Config\Field\AreaRenderer',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->areaRenderer;
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $area = $row->getArea();
        $options = [];
        if ($area) {
            $options['option_' . $this->getAreaRender()->calcOptionHash($area)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
}