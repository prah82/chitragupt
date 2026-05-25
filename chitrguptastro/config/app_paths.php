<?php
declare(strict_types=1);

// Change these two values if admin folder is moved or renamed.
define('ADMIN_DIR_NAME', 'admin');
define('ADMIN_PUBLIC_URL', ADMIN_DIR_NAME);

define('ADMIN_ABS_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . ADMIN_DIR_NAME);
define('ADMIN_UPLOADS_ABS_PATH', ADMIN_ABS_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('ADMIN_UPLOADS_PUBLIC_URL', ADMIN_PUBLIC_URL . '/uploads');
