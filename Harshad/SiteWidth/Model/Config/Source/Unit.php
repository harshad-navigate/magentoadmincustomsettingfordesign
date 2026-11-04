<?php
declare(strict_types=1);

namespace Harshad\SiteWidth\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Unit implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'px', 'label' => __('Pixels (px)')],
            ['value' => '%',  'label' => __('Percent (%)')],
        ];
    }
}
