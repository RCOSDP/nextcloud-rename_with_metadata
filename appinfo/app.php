<?php

declare(strict_types=1);

use OCA\RenameWithMetadata\AppInfo\Application;

$app = \OC::$server->get(Application::class);
$app->register();