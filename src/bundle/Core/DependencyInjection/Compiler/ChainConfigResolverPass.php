<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\Core\DependencyInjection\Compiler;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\ChainConfigResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The ChainConfigResolverPass will register all services tagged as "ibexa.site.config.resolver"
 * to the chain config resolver.
 */
class ChainConfigResolverPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(ChainConfigResolver::class)) {
            return;
        }

        $chainResolver = $container->getDefinition(ChainConfigResolver::class);

        foreach ($container->findTaggedServiceIds('ibexa.site.config.resolver') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? (int)$attributes[0]['priority'] : 0;
            // Priority range is between -255 (the lowest) and 255 (the highest)
            if ($priority > 255) {
                $priority = 255;
            }
            if ($priority < -255) {
                $priority = -255;
            }

            $chainResolver->addMethodCall(
                'addResolver',
                [
                    new Reference($id),
                    $priority,
                ]
            );
        }
    }
}

class_alias(ChainConfigResolverPass::class, 'eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainConfigResolverPass');
