<?php
namespace Harshad\TitleAlign\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Alignment implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'left',   'label' => __('Left')],
            ['value' => 'center', 'label' => __('Center')],
            ['value' => 'right',  'label' => __('Right')],
            ['value' => 'hide',   'label' => __('Hide')],
        ];
    }
}
