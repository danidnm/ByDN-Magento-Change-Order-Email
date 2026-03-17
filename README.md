# Magento 2 Change Order Email Extension

This Magento 2 extension provides a straightforward way to change the email address associated with an order directly from the Magento Admin panel. 

It is especially useful in scenarios where a guest customer entered an incorrect email, or when you need to assign a guest order to an existing registered customer.

## Features

- **Change Order Email**: Easily update the email address of any order.
- **Auto-Assign to Customer**: If the new email address belongs to an existing registered customer, the order will be automatically assigned to them.
- **Reassign as Guest**: If the new email address does not belong to any registered customer, the order becomes a guest order but with the updated email address.
- **Seamless Integration**: A new "Change email" option is added smoothly to the order view page in the admin panel.

# Installation

Run:
```bash
composer require bydn/change-order-email
./bin/magento module:enable Bydn_ChangeOrderEmail
./bin/magento setup:upgrade
```

# Usage

1. Go to **Sales -> Orders** in your Magento Admin panel.
2. View any order.
3. In the "Account Information" section (or near the customer data), look for the **Change email** row.
4. Enter the new email address and click **Change**.
5. The system will process the change and confirm whether the order was assigned to a registered customer or updated as a guest order.

<img alt="Change Order Email" width="100%" src="https://github.com/danidnm/ByDN-Magento-Change-Order-Email/blob/master/docs/magento-change-email-extension.jpg"/>

# Having Problems?

Contact me at soy at solodani.com

# License

This Magento 2 extension was created and is maintained by Daniel Navarro (https://github.com/danidnm).

If you fork, modify, or redistribute this extension, please:

- Keep the code free and open source under the same GPL-3.0 license.
- Mention the original author in your README or composer.json.
