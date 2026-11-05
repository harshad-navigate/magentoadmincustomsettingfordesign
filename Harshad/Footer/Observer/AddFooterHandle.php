<?php
declare(strict_types=1);

namespace Harshad\Footer\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class AddFooterHandle implements ObserverInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function execute(Observer $observer): void
    {
        if (!$this->scopeConfig->isSetFlag('harshad_footer/general/enabled', ScopeInterface::SCOPE_STORE)) {
            return;
        }

        $layout = $observer->getData('layout');
        if ($layout) {
            $layout->getUpdate()->addHandle('harshad_footer_enabled');
        }
    }
}
