<?php

declare(strict_types=1);

namespace OCA\RenameWithMetadata\AppInfo;

use \OCP\AppFramework\App;
use \OCA\RenameWithMetadata\Hooks\UserHooks;

class Application extends App {
    public const APP_ID = 'rename_with_metadata';

    public function __construct(array $urlParams=array()) {
        parent::__construct(self::APP_ID, $urlParams);
        $container = $this->getContainer();

        /**
         * Controllers
         **/
        $container->registerService('UserHooks', function ($c) {
            return new UserHooks(
                $c->query('ServerContainer')->getRootFolder(),
                $c->query('ServerContainer')->getDatabaseConnection(),
                $c->query('ServerContainer')->getUserSession(),
                $c->query('ServerContainer')->getMountManager(),
            );
        });
    }

    public function register() {
        $this->registerHooks();
    }

    public function registerHooks() {
        $container = $this->getContainer();
        $container->get('UserHooks')->register();
    }
}
