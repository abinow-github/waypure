<?php
declare(strict_types=1);

/**
 * Waypure Database & Core Bridge (Backward Compatibility Layer)
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

function getSessionId(): string {
    return getVisitorSessionId();
}
