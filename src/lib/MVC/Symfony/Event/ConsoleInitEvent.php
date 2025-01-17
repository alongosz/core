<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Core\MVC\Symfony\Event;

use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Allows to do things before the command is loaded, like configuring system based on input.
 */
class ConsoleInitEvent extends ConsoleEvent
{
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct(null, $input, $output);
    }
}

class_alias(ConsoleInitEvent::class, 'eZ\Publish\Core\MVC\Symfony\Event\ConsoleInitEvent');
