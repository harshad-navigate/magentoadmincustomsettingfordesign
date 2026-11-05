<?php
declare(strict_types=1);

namespace Harshad\Footer\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SectionType implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'links',      'label' => __('Links (title + links list)')],
            ['value' => 'text',       'label' => __('Text (with optional logo image)')],
            ['value' => 'image',      'label' => __('Image only')],
            ['value' => 'newsletter', 'label' => __('Newsletter')],
            ['value' => 'payment',    'label' => __('Payment Icons')],
            ['value' => 'social',     'label' => __('Social Links')],
        ];
    }
}
