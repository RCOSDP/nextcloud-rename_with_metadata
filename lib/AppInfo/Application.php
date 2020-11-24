<?php

declare(strict_types=1);

namespace OCA\RenameWithMetadata\AppInfo;

use \OCP\AppFramework\App;
use \OCA\RenameWithMetadata\Hooks\UserHooks;

class Application extends App {
    public const APP_ID = 'rename_with_metadata';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
        $container = $this->getContainer();

        /**
         * Controllers
         */
        $container->registerService('UserHooks', function() {
            $container = $this->getContainer();
            $server = $container->getServer();
            return new UserHooks(
                $server->getLogger(),
                $server->getRootFolder(),
                $server->getDatabaseConnection(),
                $server->getUserSession(),
                $server->getMountManager()
            );
        });
    }

    public function register() {
        $this->registerHooks();
    }

    public function registerHooks() {
        $container = $this->getContainer();
        $container->query('UserHooks')->register();
    }
}