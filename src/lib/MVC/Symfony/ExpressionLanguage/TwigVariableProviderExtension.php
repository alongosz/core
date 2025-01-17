<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\MVC\Symfony\ExpressionLanguage;

use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\MVC\Symfony\View\ContentView;
use Ibexa\Core\MVC\Symfony\View\VariableProviderRegistry;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class TwigVariableProviderExtension implements ExpressionFunctionProviderInterface
{
    public const PROVIDER_REGISTRY_PARAMETER = 'providerRegistry';
    public const VIEW_PARAMETER = 'view';

    /**
     * @return \Symfony\Component\ExpressionLanguage\ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'twig_variable_provider',
                static function (string $identifier) {
                    return 'Not implemented: Not a Dependency Injection expression';
                },
                function (array $variables, string $identifier) {
                    if (!$this->hasParameterProvider($variables)) {
                        throw new InvalidArgumentException(
                            self::PROVIDER_REGISTRY_PARAMETER,
                            'Expression parameter is not a valid type of ' . VariableProviderRegistry::class
                        );
                    }

                    $view = $variables[self::VIEW_PARAMETER] ?? new ContentView();
                    $providerRegistry = $variables[self::PROVIDER_REGISTRY_PARAMETER];

                    $provider = $providerRegistry->getTwigVariableProvider($identifier);

                    return $provider->getTwigVariables($view, $variables);
                }
            ),
        ];
    }

    private function hasParameterProvider(array $variables): bool
    {
        return !empty($variables[self::PROVIDER_REGISTRY_PARAMETER])
            && $variables[self::PROVIDER_REGISTRY_PARAMETER] instanceof VariableProviderRegistry;
    }
}

class_alias(TwigVariableProviderExtension::class, 'eZ\Publish\Core\MVC\Symfony\ExpressionLanguage\TwigVariableProviderExtension');
