<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Bundle\Core;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\ChainConfigResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Exception\ParameterNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\Bundle\Core\DependencyInjection\Configuration\ChainConfigResolver
 */
class ChainConfigResolverTest extends TestCase
{
    /** @var \Ibexa\Bundle\Core\DependencyInjection\Configuration\ChainConfigResolver */
    private $chainResolver;

    protected function setUp(): void
    {
        $this->chainResolver = new ChainConfigResolver();
    }

    public function testPriority()
    {
        $this->assertEquals([], $this->chainResolver->getAllResolvers());

        list($low, $high) = $this->createResolverMocks();

        $this->chainResolver->addResolver($low, 10);
        $this->chainResolver->addResolver($high, 100);

        $this->assertEquals(
            [
                $high,
                $low,
            ],
            $this->chainResolver->getAllResolvers()
        );
    }

    /**
     * Resolvers are supposed to be sorted only once.
     * This test will check that by trying to get all resolvers several times.
     */
    public function testSortResolvers()
    {
        list($low, $medium, $high) = $this->createResolverMocks();
        // We're using a mock here and not $this->chainResolver because we need to ensure that the sorting operation is done only once.
        $resolver = $this->buildMock(
            ChainConfigResolver::class,
            ['sortResolvers']
        );
        $resolver
            ->expects($this->once())
            ->method('sortResolvers')
            ->will(
                $this->returnValue(
                    [$high, $medium, $low]
                )
            );

        $resolver->addResolver($low, 10);
        $resolver->addResolver($medium, 50);
        $resolver->addResolver($high, 100);
        $expectedSortedRouters = [$high, $medium, $low];
        // Let's get all routers 5 times, we should only sort once.
        for ($i = 0; $i < 5; ++$i) {
            $this->assertSame($expectedSortedRouters, $resolver->getAllResolvers());
        }
    }

    /**
     * This test ensures that if a resolver is being added on the fly, the sorting is reset.
     */
    public function testReSortResolvers()
    {
        list($low, $medium, $high) = $this->createResolverMocks();
        $highest = clone $high;
        // We're using a mock here and not $this->chainResolver because we need to ensure that the sorting operation is done only once.
        $resolver = $this->buildMock(
            ChainConfigResolver::class,
            ['sortResolvers']
        );
        $resolver
            ->expects($this->at(0))
            ->method('sortResolvers')
            ->will(
                $this->returnValue(
                    [$high, $medium, $low]
                )
            );
        // The second time sortResolvers() is called, we're supposed to get the newly added router ($highest)
        $resolver
            ->expects($this->at(1))
            ->method('sortResolvers')
            ->will(
                $this->returnValue(
                    [$highest, $high, $medium, $low]
                )
            );

        $resolver->addResolver($low, 10);
        $resolver->addResolver($medium, 50);
        $resolver->addResolver($high, 100);
        $this->assertSame(
            [$high, $medium, $low],
            $resolver->getAllResolvers()
        );

        // Now adding another resolver on the fly, sorting must have been reset
        $resolver->addResolver($highest, 101);
        $this->assertSame(
            [$highest, $high, $medium, $low],
            $resolver->getAllResolvers()
        );
    }

    public function testGetDefaultNamespace()
    {
        $this->expectException(\LogicException::class);

        $this->chainResolver->getDefaultNamespace();
    }

    public function testSetDefaultNamespace()
    {
        $namespace = 'foo';
        foreach ($this->createResolverMocks() as $i => $resolver) {
            $resolver
                ->expects($this->once())
                ->method('setDefaultNamespace')
                ->with($namespace);
            $this->chainResolver->addResolver($resolver, $i);
        }

        $this->chainResolver->setDefaultNamespace($namespace);
    }

    public function testGetParameterInvalid()
    {
        $this->expectException(ParameterNotFoundException::class);

        $paramName = 'foo';
        $namespace = 'namespace';
        $scope = 'scope';
        foreach ($this->createResolverMocks() as $resolver) {
            $resolver
                ->expects($this->once())
                ->method('getParameter')
                ->with($paramName, $namespace, $scope)
                ->will($this->throwException(new ParameterNotFoundException($paramName, $namespace)));
            $this->chainResolver->addResolver($resolver);
        }

        $this->chainResolver->getParameter($paramName, $namespace, $scope);
    }

    /**
     * @dataProvider getParameterProvider
     *
     * @param string $paramName
     * @param string $namespace
     * @param string $scope
     * @param mixed $expectedValue
     */
    public function testGetParameter($paramName, $namespace, $scope, $expectedValue)
    {
        $resolver = $this->createMock(ConfigResolverInterface::class);
        $resolver
            ->expects($this->once())
            ->method('getParameter')
            ->with($paramName, $namespace, $scope)
            ->will($this->returnValue($expectedValue));

        $this->chainResolver->addResolver($resolver);
        $this->assertSame($expectedValue, $this->chainResolver->getParameter($paramName, $namespace, $scope));
    }

    public function getParameterProvider()
    {
        return [
            ['foo', 'namespace', 'scope', 'someValue'],
            ['some.parameter', 'wowNamespace', 'mySiteaccess', ['foo', 'bar']],
            ['another.parameter.but.longer.name', 'yetAnotherNamespace', 'anotherSiteaccess', ['foo', ['fruit' => 'apple']]],
            ['boolean.parameter', 'yetAnotherNamespace', 'admin', false],
        ];
    }

    public function testHasParameterTrue()
    {
        $paramName = 'foo';
        $namespace = 'yetAnotherNamespace';
        $scope = 'mySiteaccess';

        $resolver1 = $this->createMock(ConfigResolverInterface::class);
        $resolver1
            ->expects($this->once())
            ->method('hasParameter')
            ->with($paramName, $namespace, $scope)
            ->will($this->returnValue(false));
        $this->chainResolver->addResolver($resolver1);

        $resolver2 = $this->createMock(ConfigResolverInterface::class);
        $resolver2
            ->expects($this->once())
            ->method('hasParameter')
            ->with($paramName, $namespace, $scope)
            ->will($this->returnValue(true));
        $this->chainResolver->addResolver($resolver2);

        $resolver3 = $this->createMock(ConfigResolverInterface::class);
        $resolver3
            ->expects($this->never())
            ->method('hasParameter');
        $this->chainResolver->addResolver($resolver3);

        $this->assertTrue($this->chainResolver->hasParameter($paramName, $namespace, $scope));
    }

    public function testHasParameterFalse()
    {
        $paramName = 'foo';
        $namespace = 'yetAnotherNamespace';
        $scope = 'mySiteaccess';

        $resolver = $this->createMock(ConfigResolverInterface::class);
        $resolver
            ->expects($this->once())
            ->method('hasParameter')
            ->with($paramName, $namespace, $scope)
            ->will($this->returnValue(false));
        $this->chainResolver->addResolver($resolver);

        $this->assertFalse($this->chainResolver->hasParameter($paramName, $namespace, $scope));
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject[]
     */
    private function createResolverMocks()
    {
        return [
            $this->createMock(ConfigResolverInterface::class),
            $this->createMock(ConfigResolverInterface::class),
            $this->createMock(ConfigResolverInterface::class),
        ];
    }

    private function buildMock($class, array $methods = [])
    {
        return $this
            ->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}

class_alias(ChainConfigResolverTest::class, 'eZ\Bundle\EzPublishCoreBundle\Tests\ChainConfigResolverTest');
