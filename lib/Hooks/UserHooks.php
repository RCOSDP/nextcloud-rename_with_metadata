<?php

declare(strict_types=1);

namespace OCA\RenameWithMetadata\Hooks;

use OCP\Files\IRootFolder;
use OCP\Files\Mount\IMountManager;
use OCP\IDBConnection;
use OCP\ILogger;
use OCP\IUserSession;
use OC\Files\Filesystem;
use OC\Files\Node\Node;

use OCA\DAV\DAV\CustomPropertiesBackend;

class UserHooks {

    private $logger;
    private $rootFolder;
    private $userSession;
    private $mountManager;
    private $customPropertiesBackend;

    public function __construct(ILogger $logger,
                                IRootFolder $rootFolder,
                                IDBConnection $databaseConnection,
                                IUserSession $userSession,
                                IMountManager $mountManager) {
        $this->logger = $logger;
        $this->rootFolder = $rootFolder;
        $this->databaseConnection = $databaseConnection;
        $this->userSession = $userSession;
        $this->mountManager = $mountManager;

    }

    public function register() {
        $callback = function(Node $source, Node $target) {
            $objectTree = new \OCA\DAV\Connector\Sabre\ObjectTree();
            $userFolder = \OC::$server->getUserFolder();
            $view = \OC\Files\Filesystem::getView();

            if ($userFolder instanceof Folder && $userFolder->getPath() === $view->getRoot()) {
                $rootInfo = $userFolder;
            } else {
                $rootInfo = $view->getFileInfo('');
            }

            if ($rootInfo->getType() === 'dir') {
                $root = new \OCA\DAV\Connector\Sabre\Directory($view, $rootInfo, $objectTree);
            } else {
                $root = new \OCA\DAV\Connector\Sabre\File($view, $rootInfo);
            }
            $objectTree->init($root, $view, $this->mountManager);
            $this->customPropertiesBackend = new CustomPropertiesBackend(
                $objectTree,
                $this->databaseConnection,
                $this->userSession->getUser()
            );

            $userPrefix = '/' . $this->userSession->getUser()->getUID();
            $userPrefixLength = strlen($userPrefix);

            $filesPrefix = '/files/';
            $filesPrefixLength = strlen($filesPrefix);

            $sourcePath = $source->getPath();
            $sourcePath = substr($sourcePath, $userPrefixLength);
            $sourcePath = substr($sourcePath, $filesPrefixLength);

            $targetPath = $target->getPath();
            $targetPath = substr($targetPath, $userPrefixLength);
            $targetPath = substr($targetPath, $filesPrefixLength);

            $this->customPropertiesBackend->delete($targetPath);
            $this->customPropertiesBackend->move($sourcePath, $targetPath);
        };
        $this->rootFolder->listen('\OC\Files', 'postRename', $callback);
    }
}
