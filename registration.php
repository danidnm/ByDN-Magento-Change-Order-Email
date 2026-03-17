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

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Bydn_ChangeOrderEmail',
    __DIR__
);
