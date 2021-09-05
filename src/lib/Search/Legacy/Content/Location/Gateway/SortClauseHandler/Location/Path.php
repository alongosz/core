<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Core\Search\Legacy\Content\Location\Gateway\SortClauseHandler\Location;

use Doctrine\DBAL\Query\QueryBuilder;
use Ibexa\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;

/**
 * Content locator gateway implementation using the DoctrineDatabase.
 */
class Path extends SortClauseHandler
{
    /**
     * Check if this sort clause handler accepts to handle the given sort clause.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    public function accept(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\Location\Path;
    }

    public function applySelect(
        QueryBuilder $query,
        SortClause $sortClause,
        int $number
    ): array {
        $query
            ->addSelect(
                sprintf(
                    't.path_string AS %s',
                    $column = $this->getSortColumnName($number)
                )
            );

        return [$column];
    }
}

class_alias(Path::class, 'eZ\Publish\Core\Search\Legacy\Content\Location\Gateway\SortClauseHandler\Location\Path');