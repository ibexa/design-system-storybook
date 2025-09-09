<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\DesignSystemStorybook\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Twig\Environment;

final class StorybookController
{
    public function __construct(
        private readonly Environment $twig
    ) {
    }

    private function camelToSnake(string $camelCase): string
    {
        $pattern = '/(?<=\\w)(?=[A-Z])|(?<=[a-z])(?=[0-9])/';
        $snakeCase = preg_replace($pattern, '_', $camelCase) ?? '';

        return strtolower($snakeCase);
    }

    public function getStatus(Request $request): Response
    {
        return new Response('', Response::HTTP_OK);
    }

    public function getPreview(Request $request, string $storybookId, ?Profiler $profiler = null): Response
    {
        if ($this->shouldDisableProfiler($request)) {
            $profiler?->disable();
        }

        $previewTemplate = sprintf('@IbexaDesignSystemStorybook/themes/standard/storybook/base_preview.html.twig');
        $customIdTemplateName = $this->camelToSnake($storybookId);
        $customTemplate = sprintf('@IbexaDesignSystemStorybook/themes/standard/storybook/components/%s.html.twig', $customIdTemplateName);
        $componentTwigId = str_replace('/', ':', $customIdTemplateName);

        if ($this->twig->getLoader()->exists($customTemplate)) {
            $previewTemplate = $customTemplate;
        }

        $args = json_decode($request->query->getString('properties'), true);
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

        $context = ['args' => $transformedArgs, 'content' => $componentContent, 'component_id' => $componentTwigId];
        $content = $this->twig->render($previewTemplate, $context);

        // During development, storybook is served from a different port than the Symfony app
        // You can use nelmio/cors-bundle to set the Access-Control-Allow-Origin header correctly
        $headers = ['Access-Control-Allow-Origin' => 'http://localhost:6006'];

        return new Response($content, Response::HTTP_OK, $headers);
    }

    private function shouldDisableProfiler(Request $request): bool
    {
        return $request->headers->get('sec-fetch-dest') === 'iframe';
    }
}
