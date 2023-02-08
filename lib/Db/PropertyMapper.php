<?php

declare(strict_types=1);

namespace OCA\RenameWithMetadata\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class PropertyMapper extends QBMapper {

    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'properties', Property::class);
    }

    public function findProperties($property_path) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')->from($this->tableName)->where(
            $qb->expr()->like(
                'propertypath',
                $qb->createNamedParameter(
                    '%' . $this->db->escapeLikeParameter($property_path) . '%',
                    IQueryBuilder::PARAM_STR
                ),
                IQueryBuilder::PARAM_STR
            )
        );

        try {
            $result = $this->findEntities($qb);
        } catch (DoesNotExistException $e) {
            return null;
        }

        return $result;
    }
}
