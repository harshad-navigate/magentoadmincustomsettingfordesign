<?php
declare(strict_types=1);

namespace Harshad\Footer\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Image;

class FooterImage extends Image
{
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png', 'svg', 'webp'];
    }
}
