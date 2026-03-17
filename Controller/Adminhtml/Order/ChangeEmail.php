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

namespace Bydn\ChangeOrderEmail\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class ChangeEmail extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Sales::actions_edit';

    private $orderCollectionFactory;
    private $orderResource;
    private $customerCollectionFactory;
    private $resourceConnection;
    private $logger;

    /**
     * Constructor
     * 
     * @param Context $context
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderResource $orderResource
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        OrderCollectionFactory $orderCollectionFactory,
        OrderResource $orderResource,
        CustomerCollectionFactory $customerCollectionFactory,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderResource = $orderResource;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Modify the order email and other customer data 
    */
    public function execute()
    {
        // Get post data
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $newCustomerEmail = trim((string) $this->getRequest()->getParam('new_customer_email'));

        // Check the order ID is not empty
        if (empty($orderId)) {
            $this->messageManager->addErrorMessage(__('Invalid order ID.'));
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');
        }

        // Check the new email is not empty
        if (empty($newCustomerEmail)) {
            $this->messageManager->addErrorMessage(__('Email address cannot be empty.'));
            return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $orderId]);
        }

        // Validate the email format
        if (!filter_var($newCustomerEmail, FILTER_VALIDATE_EMAIL)) {
            $this->messageManager->addErrorMessage(__('Please enter a valid email address.'));
            return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $orderId]);
        }

        try {

            // Prepare the order to be modified (only the associated fields)
            // and check it exists
            $order = $this->getOrder($orderId);
            if (!$order || !$order->getId()) {
                $this->messageManager->addErrorMessage(__('The order does not exist.'));
                return $this->resultRedirectFactory->create()->setPath('sales/order/index');
            }

            // Check the email is changed
            if ($order->getCustomerEmail() === $newCustomerEmail) {
                $this->messageManager->addErrorMessage(__('The new email address is the same as the old one.'));
                return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $orderId]);
            }

            // Try to find a customer with that email
            $customer = $this->getCustomerByEmail($newCustomerEmail);

            // Update the order
            $this->updateOrder($order, $newCustomerEmail, $customer);

            // Update Sales Order Grid if anything changed
            $this->updateSalesOrderGrid($order);

        }
        catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('An unexpected error occurred. Please try again later.'));
        }

        return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * Returns an order
     */
    private function getOrder($orderId)
    {
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToSelect(['entity_id', 'customer_id', 'customer_is_guest', 'customer_email'])
            ->addFieldToFilter('entity_id', $orderId);
            
        $collection->getSelect()->limit(1);
        return $collection->getFirstItem();
    }

    /**
     * Get the customer data by email
     */
    private function getCustomerByEmail($email)
    {
        $collection = $this->customerCollectionFactory->create()
            ->addAttributeToSelect(['entity_id', 'group_id', 'firstname', 'lastname', 'email'])
            ->addFieldToFilter('email', $email);

        $collection->getSelect()->limit(1);
        return $collection->getFirstItem();
    }

    /**
     * Updates the order
     */
    private function updateOrder($order, $newCustomerEmail, $customer)
    {
        // If a customer exists with this email, assign the order to them
        if ($customer && $customer->getId()) {

            // We assign the order by seting the customer id, group id, is guest and email
            $order->setData('customer_id', $customer->getId());
            $order->setData('customer_is_guest', 0);
            $order->setData('customer_group_id', $customer->getData('group_id'));
            $order->setData('customer_email', $customer->getEmail());
            $this->orderResource->saveAttribute($order, 'customer_id');
            $this->orderResource->saveAttribute($order, 'customer_is_guest');
            $this->orderResource->saveAttribute($order, 'customer_group_id');
            $this->orderResource->saveAttribute($order, 'customer_email');
            
            // Notify the admin
            $this->messageManager->addSuccessMessage(__('Order successfully assigned to customer.'));
        }
        else {

            // Delete ID and group, and set customer is guest flag
            $order->setData('customer_id', null);
            $order->setData('customer_is_guest', 1);
            $order->setData('customer_group_id', 0);
            $order->setData('customer_email', $newCustomerEmail);                    
            $this->orderResource->saveAttribute($order, 'customer_id');
            $this->orderResource->saveAttribute($order, 'customer_is_guest');
            $this->orderResource->saveAttribute($order, 'customer_group_id');
            $this->orderResource->saveAttribute($order, 'customer_email');

            // Notify the admin
            $this->messageManager->addSuccessMessage(__('New email is not a customer. Order assigned to a guest email.'));
        }
    }

    /**
     * Order grid does not have models so we need to make the modifications directly with the resource
     */
    private function updateSalesOrderGrid($order)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_order_grid');
        
        $bind = [
            'customer_id' => $order->getCustomerId(),
            'customer_email' => $order->getData('customer_email'),
            'customer_group' => $order->getCustomerGroupId()
        ];

        $connection->update(
            $tableName,
            $bind,
            ['entity_id = ?' => (int) $order->getId()]
        );
    }
}
