<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\DesignSystemStorybook\Preview;

use Error;
use Ibexa\DesignSystemStorybook\Preview\PreviewView;
use PHPUnit\Framework\TestCase;

final class PreviewViewTest extends TestCase
{
    public function testItStoresTemplateAndContext(): void
    {
        $template = '@IbexaDesignSystemStorybook/themes/standard/storybook/base_preview.html.twig';
        $context = ['args' => ['foo' => 'bar'], 'content' => '<p>baz</p>', 'component_id' => 'input_text:field'];

        $view = new PreviewView($template, $context);

        self::assertSame($template, $view->template);
        self::assertSame($context, $view->context);
        self::assertArrayHasKey('args', $view->context);
        self::assertArrayHasKey('content', $view->context);
        self::assertArrayHasKey('component_id', $view->context);
    }

    public function testContextIsNotAffectedByExternalMutations(): void
    {
        $template = 'any.html.twig';
        $original = ['args' => ['foo' => 1]];

        $view = new PreviewView($template, $original);

        $original['args']['foo'] = 2;
        $original['new'] = 'value';

        self::assertSame(1, $view->context['args']['foo']);
        self::assertArrayNotHasKey('new', $view->context);
    }

    public function testReadonlyTemplatePropertyCannotBeReassigned(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessageMatches('/Cannot modify readonly property/');

        $view = new PreviewView('a.html.twig', []);
        // @phpstan-ignore-next-line
        $view->template = 'b.html.twig';
    }

    public function testReadonlyContextPropertyCannotBeReassigned(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessageMatches('/Cannot modify readonly property/');

        $view = new PreviewView('a.html.twig', ['x' => 1]);
        // @phpstan-ignore-next-line
        $view->context = [];
    }

    public function testEmptyTemplateStringIsAccepted(): void
    {
        $view = new PreviewView('', []);

        self::assertSame('', $view->template);
        self::assertSame([], $view->context);
    }
}
