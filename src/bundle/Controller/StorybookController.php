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

    public function getPreview(Request $request, string $storybookId, ?Profiler $profiler): Response
    {
        if ($request->server->get('HTTP_SEC_FETCH_DEST') === 'iframe') {
            $profiler->disable();
        }

        $previewTemplate = sprintf('@IbexaDesignSystemStorybook/themes/standard/storybook/base_preview.html.twig');
        $customIdTemplateName = $this->camelToSnake($storybookId);
        $customTemplate = sprintf('@IbexaDesignSystemStorybook/themes/standard/storybook/components/%s.html.twig', $customIdTemplateName);
        $componentTwigId = str_replace('/', ':', $storybookId);

        if ($this->twig->getLoader()->exists($customTemplate)) {
            $previewTemplate = $customTemplate;
        }

        $camelCaseArgs = json_decode($request->query->getString('properties'), true);
        $componentContent = null;
        $snakeCaseArgs = [];

        foreach ($camelCaseArgs as $camelCaseKey => $value) {
            if ($camelCaseKey == 'children') {
                $componentContent = $value;

                continue;
            } elseif ($camelCaseKey == 'className') {
                $snakeCaseArgs['class'] = $value;

                continue;
            }

            $snakeCaseKey = $this->camelToSnake($camelCaseKey);

            $snakeCaseArgs[$snakeCaseKey] = $value;
        }

        $context = ['args' => $snakeCaseArgs, 'content' => $componentContent, 'component_id' => $componentTwigId];
        $content = $this->twig->render($previewTemplate, $context);

        // During development, storybook is served from a different port than the Symfony app
        // You can use nelmio/cors-bundle to set the Access-Control-Allow-Origin header correctly
        $headers = ['Access-Control-Allow-Origin' => 'http://localhost:6006'];

        return new Response($content, Response::HTTP_OK, $headers);
    }
}
