<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Bundle\Core\Imagine\Cache\Resolver;

use Ibexa\Bundle\Core\Imagine\Cache\Resolver\RelativeResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use PHPUnit\Framework\TestCase;

class RelativeResolverTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface */
    private $liipResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->liipResolver = $this->getMockBuilder(ResolverInterface::class)->getMock();
    }

    public function testResolve()
    {
        $resolver = new RelativeResolver($this->liipResolver);

        $path = '7/4/2/0/247-1-eng-GB/test.png';
        $filter = 'big';

        $absolute = 'http://ibexa.co/var/site/storage/images/_aliases/big/7/4/2/0/247-1-eng-GB/test.png';
        $expected = '/var/site/storage/images/_aliases/big/7/4/2/0/247-1-eng-GB/test.png';

        $this->liipResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($path, $filter)
            ->willReturn($absolute);

        $this->assertSame($expected, $resolver->resolve($path, $filter));
    }
}

class_alias(RelativeResolverTest::class, 'eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Cache\Resolver\RelativeResolverTest');
