<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Core\Persistence\Legacy\URL;

use Ibexa\Contracts\Core\Persistence\URL\Handler as HandlerInterface;
use Ibexa\Contracts\Core\Persistence\URL\URLUpdateStruct;
use Ibexa\Contracts\Core\Repository\Values\URL\URLQuery;
use Ibexa\Core\Base\Exceptions\NotFoundException;

/**
 * Storage Engine handler for URLs.
 */
class Handler implements HandlerInterface
{
    /** @var \Ibexa\Core\Persistence\Legacy\URL\Gateway */
    private $urlGateway;

    /** @var \Ibexa\Core\Persistence\Legacy\URL\Mapper */
    private $urlMapper;

    /**
     * Handler constructor.
     *
     * @param \Ibexa\Core\Persistence\Legacy\URL\Gateway $gateway
     * @param \Ibexa\Core\Persistence\Legacy\URL\Mapper $mapper
     */
    public function __construct(Gateway $gateway, Mapper $mapper)
    {
        $this->urlGateway = $gateway;
        $this->urlMapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function updateUrl($id, URLUpdateStruct $urlUpdateStruct)
    {
        $url = $this->urlMapper->createURLFromUpdateStruct(
            $urlUpdateStruct
        );
        $url->id = $id;

        $this->urlGateway->updateUrl($url);

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function find(URLQuery $query)
    {
        $results = $this->urlGateway->find(
            $query->filter,
            $query->offset,
            $query->limit,
            $query->sortClauses,
            $query->performCount
        );

        return [
            'count' => $results['count'],
            'items' => $this->urlMapper->extractURLsFromRows($results['rows']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function loadById($id)
    {
        $url = $this->urlMapper->extractURLsFromRows(
            $this->urlGateway->loadUrlData($id)
        );

        if (count($url) < 1) {
            throw new NotFoundException('URL', $id);
        }

        return reset($url);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUrl($url)
    {
        $urls = $this->urlMapper->extractURLsFromRows(
            $this->urlGateway->loadUrlDataByUrl($url)
        );

        if (count($urls) < 1) {
            throw new NotFoundException('URL', $url);
        }

        return reset($urls);
    }

    /**
     * {@inheritdoc}
     */
    public function findUsages($id)
    {
        $ids = $this->urlGateway->findUsages($id);

        return $ids;
    }
}

class_alias(Handler::class, 'eZ\Publish\Core\Persistence\Legacy\URL\Handler');
