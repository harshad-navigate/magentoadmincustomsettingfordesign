<?php
declare(strict_types=1);

namespace Harshad\Slider\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Image;

class SliderImage extends Image
{
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png', 'svg', 'webp', 'avif'];
    }
}
