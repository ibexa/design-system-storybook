<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\DesignSystemStorybook\Preview;

use Generator;
use Ibexa\DesignSystemStorybook\Preview\PreviewView;
use Ibexa\DesignSystemStorybook\Preview\TwigPreviewRenderer;
use PHPUnit\Framework\TestCase;
use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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

    /**
     * @dataProvider twigExceptionProvider
     */
    public function testRenderBubblesUpTwigExceptions(Throwable $exception): void
    {
        $template = 'any.html.twig';
        $context = ['args' => []];

        $this->twig
            ->method('render')
            ->willThrowException($exception);

        $renderer = new TwigPreviewRenderer($this->twig);
        $view = new PreviewView($template, $context);

        $this->expectException($exception::class);

        $renderer->render($view);
    }

    /**
     * @return Generator<string, array{0: \Throwable}>
     */
    public function twigExceptionProvider(): Generator
    {
        yield 'loader' => [new LoaderError('template not found')];
        yield 'runtime' => [new RuntimeError('runtime error')];
        yield 'syntax' => [new SyntaxError('syntax error')];
    }
}
