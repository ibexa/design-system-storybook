<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\DesignSystemStorybook\Preview;

use Ibexa\DesignSystemStorybook\ComponentsResolver;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

final readonly class PreviewViewBuilder
{
    public function __construct(
        private Environment $twig,
        private ComponentsResolver $componentsResolver
    ) {
    }

    public function build(Request $request, string $storybookId): PreviewView
    {
        $componentId = $this->componentsResolver->resolve($storybookId);

        $previewTemplate = '@IbexaDesignSystemStorybook/themes/standard/storybook/base_preview.html.twig';
        $customTemplate = $this->getCustomTemplatePath($componentId);

        if ($this->twig->getLoader()->exists($customTemplate)) {
            $previewTemplate = $customTemplate;
        }

        $argsJson = $request->query->getString('properties');
        $args = $argsJson !== '' ? json_decode($argsJson, true, 512, JSON_THROW_ON_ERROR) : [];
        $customParametersJson = $request->query->getString('customParameters');
        $parameters = $customParametersJson !== '' ? json_decode($customParametersJson, true, 512, JSON_THROW_ON_ERROR) : [];

        $componentContent = null;
        $transformedArgs = [];

        foreach ($args as $key => $value) {
            if ($key === 'children') {
                $componentContent = $value;
                continue;
            }
            if ($key === 'className') {
                $transformedArgs['class'] = $value;
                continue;
            }
            $transformedArgs[$key] = $value;
        }

        $context = [
            'args' => $transformedArgs,
            'content' => $componentContent,
            'component_id' => $componentId,
            'parameters' => $parameters,
        ];

        return new PreviewView($previewTemplate, $context);
    }

    private function getCustomTemplatePath(string $componentId): string
    {
        $customIdTemplateName = str_replace(':', '/', $componentId);

        return sprintf(
            '@IbexaDesignSystemStorybook/themes/standard/storybook/components/%s.html.twig',
            $customIdTemplateName
        );
    }
}
