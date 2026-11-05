<?php
declare(strict_types=1);

namespace Harshad\Footer\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class LinksField extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn('label', ['label' => __('Link Title'), 'class' => 'required-entry']);
        $this->addColumn('url', ['label' => __('URL (https://... or /page-url)'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Link');
    }
}
