<?php

declare(strict_types=1);

namespace OCA\RenameWithMetadata\Hooks;

use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\Mount\IMountManager;
use OCP\IDBConnection;
use OCP\IUserSession;
use OC\Files\Filesystem;
use OC\Files\Node\Node;

use OCA\RenameWithMetadata\Db\PropertyMapper;

use OCA\DAV\DAV\CustomPropertiesBackend;

class UserHooks {
    private $rootFolder;
    private $databaseConnection;
    private $userSession;
    private $mountManager;
    private $customPropertiesBackend;
    private $mapper;

    public function __construct(IRootFolder $rootFolder,
                                IDBConnection $databaseConnection,
                                IUserSession $userSession,
                                IMountManager $mountManager) {
        $this->rootFolder = $rootFolder;
        $this->databaseConnection = $databaseConnection;
        $this->userSession = $userSession;
        $this->mountManager = $mountManager;
        $this->mapper = new PropertyMapper($databaseConnection);

    }

    public function register() {
        $callback = function(Node $source, Node $target) {
            $objectTree = new \OCA\DAV\Connector\Sabre\ObjectTree();
            $userFolder = \OC::$server->getUserFolder();
            $view = Filesystem::getView();

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
            $dump_sourcePath = 'files' . $userPrefix . '/' . $sourcePath;
            $dump_sourcePathLength = strlen($dump_sourcePath);
            $dump_targetPath = 'files' . $userPrefix . '/' . $targetPath;
            $entities = $this->mapper->findProperties($dump_sourcePath);

            if (!empty($entities)) {
                foreach ($entities as $entity) {
                    $property_path = $entity->getPropertypath();
                    if (str_contains($property_path, $dump_sourcePath)) {
                        $value = substr($property_path, $dump_sourcePathLength);
                        $new_sourcePath = $dump_sourcePath . $value;
                        $new_targetPath = $dump_targetPath . $value;
                        $this->customPropertiesBackend->delete($targetPath);
                        $this->customPropertiesBackend->move($new_sourcePath, $new_targetPath);
                    }
                }
            }
        };
        $this->rootFolder->listen('\OC\Files', 'postRename', $callback);
    }
}
