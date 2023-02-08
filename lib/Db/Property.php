<?php

declare(strict_types=1);

namespace OCA\RenameWithMetadata\Db;

use OCP\AppFramework\Db\Entity;

class Property extends Entity {
    protected $propertypath;
    protected $propertyname;
    protected $userid;
    protected $propertyvalue;
    protected $valuetype;
    public function __construct() {}
}
