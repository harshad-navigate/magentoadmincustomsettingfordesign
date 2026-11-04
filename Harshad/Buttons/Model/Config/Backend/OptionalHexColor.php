<?php
declare(strict_types=1);

namespace Harshad\Buttons\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class OptionalHexColor extends Value
{
    public function beforeSave()
    {
        $value = trim((string) $this->getValue());

        if ($value !== '' && !preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            throw new LocalizedException(
                __('Please enter a valid HEX color, for example #1d4ed8, or leave the field empty.')
            );
        }

        $this->setValue(strtolower($value));
        return parent::beforeSave();
    }
}
