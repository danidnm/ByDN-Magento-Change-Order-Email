<?php
namespace Bydn\ChangeOrderEmail\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class ChangeEmail extends Template
{
    private $coreRegistry;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Get current order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Get form action url
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('sales/order/changeEmail');
    }
}
