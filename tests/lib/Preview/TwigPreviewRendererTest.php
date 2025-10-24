<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\DesignSystemStorybook\Preview;

use Ibexa\DesignSystemStorybook\Preview\PreviewView;
use Ibexa\DesignSystemStorybook\Preview\TwigPreviewRenderer;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\LoaderError;

final class TwigPreviewRendererTest extends TestCase
{
    /** @var \Twig\Environment&\PHPUnit\Framework\MockObject\MockObject */
    private Environment $twig;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
    }

    public function testRenderDelegatesToTwigAndReturnsHtml(): void
    {
        $template = '@IbexaDesignSystemStorybook/themes/standard/storybook/base_preview.html.twig';
        $context = [
            'args' => ['label' => 'Name', 'class' => 'my-class'],
            'content' => '<span>Inner</span>',
            'component_id' => 'input_text:field',
        ];

        $expectedHtml = '<div>Rendered!</div>';

        $this->twig
            ->expects(self::once())
            ->method('render')
            ->with($template, $context)
            ->willReturn($expectedHtml);

        $renderer = new TwigPreviewRenderer($this->twig);
        $view = new PreviewView($template, $context);

        $html = $renderer->render($view);

        self::assertSame($expectedHtml, $html);
    }

    public function testRenderBubblesUpTwigException(): void
    {
        $template = 'any.html.twig';
        $context = ['args' => []];
        $exception = new LoaderError('template not found');

        $this->twig
            ->expects(self::once())
            ->method('render')
            ->with($template, $context)
            ->willThrowException($exception);

        $renderer = new TwigPreviewRenderer($this->twig);
        $view = new PreviewView($template, $context);

        $this->expectExceptionObject($exception);

        $renderer->render($view);
    }
}
