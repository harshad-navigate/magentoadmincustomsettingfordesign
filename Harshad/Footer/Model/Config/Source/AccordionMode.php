<?php
declare(strict_types=1);

namespace Harshad\Footer\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AccordionMode implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 0, 'label' => __('No Accordion')],
            ['value' => 1, 'label' => __('Mobile only (below 768px)')],
            ['value' => 2, 'label' => __('Mobile and Tablet (below 1024px)')],
        ];
    }
}
