<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Core\MVC\Symfony\Templating\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Ibexa\Core\MVC\Symfony\Templating\GlobalHelper;
use Twig\Extension\GlobalsInterface;

class CoreExtension extends AbstractExtension implements GlobalsInterface
{
    /** @var \Ibexa\Core\MVC\Symfony\Templating\GlobalHelper */
    private $globalHelper;

    public function __construct(GlobalHelper $globalHelper)
    {
        $this->globalHelper = $globalHelper;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'ezpublish.core';
    }

    /**
     * @return array
     */
    public function getGlobals(): array
    {
        return ['ezplatform' => $this->globalHelper];
    }
}

class_alias(CoreExtension::class, 'eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\CoreExtension');