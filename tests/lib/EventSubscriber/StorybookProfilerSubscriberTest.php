<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\DesignSystemStorybook\EventSubscriber;

use Ibexa\DesignSystemStorybook\EventSubscriber\StorybookProfilerSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Profiler\Profiler;

final class StorybookProfilerSubscriberTest extends TestCase
{
    public function testSubscribedEventsRegistersOnKernelRequest(): void
    {
        $events = StorybookProfilerSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(KernelEvents::REQUEST, $events);
        $listenerDefinition = $events[KernelEvents::REQUEST];

        self::assertIsArray($listenerDefinition);
        self::assertSame('onKernelRequest', $listenerDefinition[0]);
    }

    public function testDisablesProfilerForStorybookIframe(): void
    {
        $profiler = $this->createMock(Profiler::class);
        $profiler->expects(self::once())->method('disable');

        $subscriber = new StorybookProfilerSubscriber($profiler);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create(
            '/storybook/preview/AltRadio/AltRadioInput',
            'GET',
            [],
            [],
            [],
            ['HTTP_SEC_FETCH_DEST' => 'iframe'] // Symfony normalizes hyphenated headers
        );

        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);
    }

    public function testDoesNotDisableProfilerForNonIframe(): void
    {
        $profiler = $this->createMock(Profiler::class);
        $profiler->expects(self::never())->method('disable');

        $subscriber = new StorybookProfilerSubscriber($profiler);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/storybook/preview/AltRadio/AltRadioInput', 'GET');
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);
    }

    public function testDoesNotDisableProfilerWhenPathDoesNotMatch(): void
    {
        $profiler = $this->createMock(Profiler::class);
        $profiler->expects(self::never())->method('disable');

        $subscriber = new StorybookProfilerSubscriber($profiler);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/some/other/path', 'GET', [], [], [], ['HTTP_SEC_FETCH_DEST' => 'iframe']);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);
    }

    public function testDoesNothingForSubRequest(): void
    {
        $profiler = $this->createMock(Profiler::class);
        $profiler->expects(self::never())->method('disable');

        $subscriber = new StorybookProfilerSubscriber($profiler);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/storybook/preview/AltRadio/AltRadioInput', 'GET', [], [], [], ['HTTP_SEC_FETCH_DEST' => 'iframe']);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $subscriber->onKernelRequest($event);
    }

    public function testDoesNothingWhenProfilerIsNull(): void
    {
        $subscriber = new StorybookProfilerSubscriber(null);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/storybook/preview/AltRadio/AltRadioInput', 'GET', [], [], [], ['HTTP_SEC_FETCH_DEST' => 'iframe']);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);
        $this->addToAssertionCount(1); // no exception = pass
    }
}
