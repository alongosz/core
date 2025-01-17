<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Core\Persistence\Content;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\ValueObject;

/**
 * Content item Value Object - a composite of Content and Type instances.
 *
 * @property-read \Ibexa\Contracts\Core\Persistence\Content $content
 * @property-read \Ibexa\Contracts\Core\Persistence\Content\ContentInfo $contentInfo
 * @property-read \Ibexa\Contracts\Core\Persistence\Content\Type $type
 */
final class ContentItem extends ValueObject
{
    /** @var \Ibexa\Contracts\Core\Persistence\Content */
    protected $content;

    /** @var \Ibexa\Contracts\Core\Persistence\Content\ContentInfo */
    protected $contentInfo;

    /** @var \Ibexa\Contracts\Core\Persistence\Content\Type */
    protected $type;

    /**
     * @internal for internal use by Repository Storage abstraction
     */
    public function __construct(Content $content, ContentInfo $contentInfo, Type $type)
    {
        parent::__construct([]);
        $this->content = $content;
        $this->contentInfo = $contentInfo;
        $this->type = $type;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function getType(): Type
    {
        return $this->type;
    }
}

class_alias(ContentItem::class, 'eZ\Publish\SPI\Persistence\Content\ContentItem');
