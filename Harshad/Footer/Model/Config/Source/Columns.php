<?php
declare(strict_types=1);

namespace Harshad\Footer\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Columns implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $options = [];
        for ($i = 1; $i <= 6; $i++) {
            $options[] = ['value' => $i, 'label' => (string) $i];
        }
        return $options;
    }
}
