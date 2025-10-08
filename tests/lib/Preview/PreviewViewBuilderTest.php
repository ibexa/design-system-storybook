<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\DesignSystemStorybook\Preview;

use Ibexa\DesignSystemStorybook\ComponentsResolver;
use Ibexa\DesignSystemStorybook\Preview\PreviewViewBuilder;
use JsonException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

final class PreviewViewBuilderTest extends TestCase
{
    /** @var \Twig\Environment&\PHPUnit\Framework\MockObject\MockObject */
    private Environment $twig;

    /** @var \Twig\Loader\LoaderInterface&\PHPUnit\Framework\MockObject\MockObject */
    private LoaderInterface $loader;

    private ComponentsResolver $resolver;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->loader = $this->createMock(LoaderInterface::class);
        $this->resolver = new ComponentsResolver();

        $this->twig
            ->method('getLoader')
            ->willReturn($this->loader);
    }

    public function testBuildUsesBaseTemplateWhenCustomTemplateDoesNotExist(): void
    {
        $storybookId = 'components/InputText/InputTextField';
        $componentId = 'input_text:field';

        $this->loader
            ->method('exists')
            ->willReturn(false);

        $request = Request::create('/preview', 'GET', [
            'properties' => json_encode(['label' => 'Name'], JSON_THROW_ON_ERROR),
        ]);

        $builder = new PreviewViewBuilder($this->twig, $this->resolver);
        $view = $builder->build($request, $storybookId);

        self::assertSame(
            '@IbexaDesignSystemStorybook/themes/standard/storybook/base_preview.html.twig',
            $view->template
        );
        self::assertSame(
            [
                'args' => ['label' => 'Name'],
                'content' => null,
                'component_id' => $componentId,
            ],
            $view->context
        );
    }

    public function testBuildUsesCustomTemplateWhenExistsAndTransformsArgs(): void
    {
        $storybookId = 'components/InputText/InputTextField';
        $componentId = 'input_text:field';

        $customTemplate = '@IbexaDesignSystemStorybook/themes/standard/storybook/components/input_text/field.html.twig';

        $this->loader
            ->expects(self::once())
            ->method('exists')
            ->with($customTemplate)
            ->willReturn(true);

        $props = [
            'className' => 'my-klass',
            'children' => '<span>Inner</span>',
            'placeholder' => 'Type here…',
        ];

        $request = Request::create('/preview', 'GET', [
            'properties' => json_encode($props, JSON_THROW_ON_ERROR),
        ]);

        $builder = new PreviewViewBuilder($this->twig, $this->resolver);
        $view = $builder->build($request, $storybookId);

        self::assertSame($customTemplate, $view->template, 'Should pick custom template when it exists');

        self::assertSame(
            [
                'args' => [
                    'class' => 'my-klass',
                    'placeholder' => 'Type here…',
                ],
                'content' => '<span>Inner</span>',
                'component_id' => $componentId,
            ],
            $view->context
        );
    }

    public function testBuildWithNoPropertiesQueryParamYieldsEmptyArgsAndNullContent(): void
    {
        $storybookId = 'components/Checkbox/CheckboxInput';
        $componentId = 'checkbox:input';

        $this->loader
            ->method('exists')
            ->willReturn(false);

        $request = Request::create('/preview', 'GET');

        $builder = new PreviewViewBuilder($this->twig, $this->resolver);
        $view = $builder->build($request, $storybookId);

        self::assertSame(
            '@IbexaDesignSystemStorybook/themes/standard/storybook/base_preview.html.twig',
            $view->template
        );

        self::assertSame(
            [
                'args' => [],
                'content' => null,
                'component_id' => $componentId,
            ],
            $view->context
        );
    }

    public function testBuildThrowsOnInvalidJson(): void
    {
        $storybookId = 'components/InputText/InputTextField';

        $this->loader
            ->method('exists')
            ->willReturn(false);

        $request = Request::create('/preview', 'GET', [
            'properties' => '{"label":"Name",}',
        ]);

        $builder = new PreviewViewBuilder($this->twig, $this->resolver);

        $this->expectException(JsonException::class);
        $builder->build($request, $storybookId);
    }
}
