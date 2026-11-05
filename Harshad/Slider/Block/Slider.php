<?php
declare(strict_types=1);

namespace Harshad\Slider\Block;

use Harshad\Slider\ViewModel\Slider as SliderViewModel;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Slider extends Template
{
    private static int $counter = 0;
    private static bool $cssPrinted = false;
    private static bool $jsPrinted = false;

    public function __construct(
        Context $context,
        private readonly SliderViewModel $viewModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getViewModel(): SliderViewModel
    {
        return $this->viewModel;
    }

    public function nextId(): string
    {
        return 'hs-' . (++self::$counter);
    }

    /** CSS is printed only once per page, even with several sliders */
    public function shouldPrintCss(): bool
    {
        if (self::$cssPrinted) {
            return false;
        }
        return self::$cssPrinted = true;
    }

    /** JS is printed only once per page, even with several sliders */
    public function shouldPrintJs(): bool
    {
        if (self::$jsPrinted) {
            return false;
        }
        return self::$jsPrinted = true;
    }

    /** A block placed by layout (position start/end) must match the admin position */
    public function isAllowed(): bool
    {
        $vm = $this->viewModel;
        if (!$vm->isEnabled()) {
            return false;
        }
        $position = (string) $this->getData('position');
        return $position === '' || $position === $vm->getPosition();
    }
}
