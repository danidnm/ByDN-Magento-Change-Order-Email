<?php
/**
 * @package     Bydn_ChangeOrderEmail
 * @author      Daniel Navarro <https://github.com/danidnm>
 * @license     GPL-3.0-or-later
 * @copyright   Copyright (c) 2025 Daniel Navarro
 *
 * This file is part of a free software package licensed under the
 * GNU General Public License v3.0.
 * You may redistribute and/or modify it under the same license.
 */

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
