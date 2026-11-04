<?php
declare(strict_types=1);

namespace Harshad\ThemeColor\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class ColorPicker extends Field
{
    private SecureHtmlRenderer $htmlSecureRenderer;

    public function __construct(
        Context $context,
        SecureHtmlRenderer $htmlSecureRenderer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->htmlSecureRenderer = $htmlSecureRenderer;
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $id    = $element->getHtmlId();
        $value = (string) $element->getValue();
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            $value = '#000000';
        }

        $html = $element->getElementHtml();
        $html .= '<input type="color" id="' . $this->escapeHtmlAttr($id) . '_picker"'
            . ' value="' . $this->escapeHtmlAttr($value) . '"'
            . ' style="margin-left:10px;width:55px;height:34px;padding:0;'
            . 'border:1px solid #adadad;vertical-align:middle;cursor:pointer;"/>';

        $js = "(function(){
            var t=document.getElementById('" . $this->escapeJs($id) . "'),
                p=document.getElementById('" . $this->escapeJs($id) . "_picker');
            if(!t||!p){return;}
            p.addEventListener('input',function(){t.value=p.value;});
            t.addEventListener('input',function(){
                if(/^#[0-9a-fA-F]{6}$/.test(t.value)){p.value=t.value;}
            });
        })();";

        return $html . $this->htmlSecureRenderer->renderTag('script', [], $js, false);
    }
}
