<?php
declare(strict_types=1);

namespace Harshad\Slider\Model\Config\Source;

class Options
{
    public function position(): array
    {
        return [
            ['value' => 'start', 'label' => __('Home page - Start (above content)')],
            ['value' => 'end',   'label' => __('Home page - End (below content)')],
            ['value' => 'cms',   'label' => __('Only via CMS block / widget (manual)')],
        ];
    }

    public function widthMode(): array
    {
        return [
            ['value' => 'boxed', 'label' => __('Boxed (inside site container)')],
            ['value' => 'full',  'label' => __('Full Width')],
        ];
    }

    public function navType(): array
    {
        return [
            ['value' => 'none',   'label' => __('None')],
            ['value' => 'arrows', 'label' => __('Arrows')],
            ['value' => 'dots',   'label' => __('Dots')],
            ['value' => 'both',   'label' => __('Arrows and Dots')],
        ];
    }

    public function arrowPos(): array
    {
        return [
            ['value' => 'sides', 'label' => __('Sides (left and right, middle)')],
            ['value' => 'bl',    'label' => __('Bottom Left')],
            ['value' => 'bc',    'label' => __('Bottom Center')],
            ['value' => 'br',    'label' => __('Bottom Right')],
        ];
    }

    public function dotsPos(): array
    {
        return [
            ['value' => 'c', 'label' => __('Bottom Center')],
            ['value' => 'l', 'label' => __('Bottom Left')],
            ['value' => 'r', 'label' => __('Bottom Right')],
        ];
    }

    public function hAlign(): array
    {
        return [
            ['value' => 'left',   'label' => __('Left')],
            ['value' => 'center', 'label' => __('Center')],
            ['value' => 'right',  'label' => __('Right')],
        ];
    }

    public function vAlign(): array
    {
        return [
            ['value' => 'top',    'label' => __('Top')],
            ['value' => 'middle', 'label' => __('Middle')],
            ['value' => 'bottom', 'label' => __('Bottom')],
        ];
    }

    public function buttonStyle(): array
    {
        return [
            ['value' => 'primary',   'label' => __('Primary')],
            ['value' => 'secondary', 'label' => __('Secondary')],
        ];
    }
}
