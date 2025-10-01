<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\DesignSystemStorybook\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Profiler\Profiler;

final class StorybookProfilerSubscriber implements EventSubscriberInterface
{
    private const string PREVIEW_PATH_PREFIX = '/storybook/preview';

    public function __construct(
        private readonly ?Profiler $profiler = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || $this->profiler === null) {
            return;
        }

        $request = $event->getRequest();

        if (
            str_starts_with($request->getPathInfo(), self::PREVIEW_PATH_PREFIX)
            && $request->headers->get('sec-fetch-dest') === 'iframe'
        ) {
            $this->profiler->disable();
        }
    }
}
