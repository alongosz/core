<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Bundle\Core\Imagine\Cache\Resolver;

use Ibexa\Bundle\Core\Imagine\Cache\Resolver\ProxyResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use PHPUnit\Framework\TestCase;

class ProxyResolverTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface */
    private $resolver;

    /** @var string */
    private $path;

    /** @var string */
    private $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = $this->getMockBuilder(ResolverInterface::class)->getMock();
        $this->path = '7/4/2/0/247-1-eng-GB/img_0885.jpg';
        $this->filter = 'medium';
    }

    public function testResolveUsingProxyHostWithTrailingSlash()
    {
        $hosts = ['http://ezplatform.com/'];
        $proxyResolver = new ProxyResolver($this->resolver, $hosts);

        $resolvedPath = 'http://ibexa.co/var/site/storage/images/_aliases/medium/7/4/2/0/247-1-eng-GB/img_0885.jpg';

        $this->resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->willReturn($resolvedPath);

        $expected = 'http://ezplatform.com/var/site/storage/images/_aliases/medium/7/4/2/0/247-1-eng-GB/img_0885.jpg';

        $this->assertEquals($expected, $proxyResolver->resolve($this->path, $this->filter));
    }

    public function testResolveAndRemovePortUsingProxyHost()
    {
        $hosts = ['http://ibexa.co'];
        $proxyResolver = new ProxyResolver($this->resolver, $hosts);

        $resolvedPath = 'http://ibexa.co:8060/var/site/storage/images/_aliases/medium/7/4/2/0/247-1-eng-GB/img_0885.jpg';

        $this->resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->willReturn($resolvedPath);

        $expected = 'http://ibexa.co/var/site/storage/images/_aliases/medium/7/4/2/0/247-1-eng-GB/img_0885.jpg';

        $this->assertEquals($expected, $proxyResolver->resolve($this->path, $this->filter));
    }

    public function testResolveAndRemovePortUsingProxyHostWithTrailingSlash()
    {
        $hosts = ['http://ibexa.co'];
        $proxyResolver = new ProxyResolver($this->resolver, $hosts);

        $resolvedPath = 'http://ezplatform.com:8080/var/site/storage/images/_aliases/medium/7/4/2/0/247-1-eng-GB/img_0885.jpg';

        $this->resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->willReturn($resolvedPath);

        $expected = 'http://ibexa.co/var/site/storage/images/_aliases/medium/7/4/2/0/247-1-eng-GB/img_0885.jpg';

        $this->assertEquals($expected, $proxyResolver->resolve($this->path, $this->filter));
    }
}

class_alias(ProxyResolverTest::class, 'eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Cache\Resolver\ProxyResolverTest');
