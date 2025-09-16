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
    /** @var array<string, string> */
    private static array $componentsMap = [
        'AltRadio/AltRadioInput' => 'alt_radio:input',
        'Checkbox/CheckboxField' => 'checkbox:field',
        'Checkbox/CheckboxInput' => 'checkbox:input',
        'Checkbox/CheckboxesListField' => 'checkbox:list_field',
        'InputText/InputTextField' => 'input_text:field',
        'InputText/InputTextInput' => 'input_text:input',
        'RadioButton/RadioButtonField' => 'radio_button:field',
        'RadioButton/RadioButtonInput' => 'radio_button:input',
        'RadioButton/RadioButtonsListField' => 'radio_button:list_field',
    ];

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

    private function getComponentId(string $storybookId): string
    {
        $cleanStorybookId = str_replace('components/', '', $storybookId);

        return self::$componentsMap[$cleanStorybookId] ?? $this->camelToSnake($cleanStorybookId);
    }

    private function getCustomTemplatePath(string $componentId): string
    {
        $customIdTemplateName = str_replace(':', '/', $componentId);

        return sprintf('@IbexaDesignSystemStorybook/themes/standard/storybook/components/%s.html.twig', $customIdTemplateName);
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
        $componentId = $this->getComponentId($storybookId);
        $customTemplate = $this->getCustomTemplatePath($componentId);

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

        $context = ['args' => $transformedArgs, 'content' => $componentContent, 'component_id' => $componentId];
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
