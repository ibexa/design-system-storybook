<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\DesignSystemStorybook\Controller;

use Ibexa\DesignSystemStorybook\ComponentsResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class StorybookController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ComponentsResolver $componentsResolver
    ) {
    }

    public function getStatus(): Response
    {
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function getPreview(Request $request, string $storybookId): Response
    {
        $previewTemplate = sprintf('@IbexaDesignSystemStorybook/themes/standard/storybook/base_preview.html.twig');
        $componentId = $this->componentsResolver->resolve($storybookId);
        $customTemplate = $this->getCustomTemplatePath($componentId);

        if ($this->twig->getLoader()->exists($customTemplate)) {
            $previewTemplate = $customTemplate;
        }

        $args = json_decode($request->query->getString('properties'), true);
        $parameters = json_decode($request->query->getString('customParameters'), true);
        $componentContent = null;
        $transformedArgs = [];

        foreach ($args as $key => $value) {
            if ($key == 'children') {
                $componentContent = $value;

                continue;
            } elseif ($key == 'className') {
                $transformedArgs['class'] = $value;

                continue;
            }

            $transformedArgs[$key] = $value;
        }

        $context = ['args' => $transformedArgs, 'content' => $componentContent, 'component_id' => $componentId, 'parameters' => $parameters];
        $content = $this->twig->render($previewTemplate, $context);

        // During development, storybook is served from a different port than the Symfony app
        // You can use nelmio/cors-bundle to set the Access-Control-Allow-Origin header correctly
        $headers = ['Access-Control-Allow-Origin' => 'http://localhost:6006'];

        return new Response($content, Response::HTTP_OK, $headers);
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
