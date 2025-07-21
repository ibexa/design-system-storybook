<?php

namespace Ibexa\Bundle\DesignSystemStorybook\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class StorybookController
{
    /** @var \Twig\Environment */
    // protected $twig;

    public function __construct(
        Environment $twig,
    ) {
        $this->twig = $twig;
    }

    public function getStatus(Request $request): Response
    {
        return new Response('', Response::HTTP_OK);
    }
    
    public function getPreview(Request $request, string $storybookId): Response
    {
        $previewTemplate = sprintf('@IbexaDesignSystemStorybook/themes/standard/storybook/preview.html.twig');
        $storybookComponentTemplate = sprintf('@IbexaDesignSystemStorybook/themes/standard/storybook/components/%s.html.twig', $storybookId);
        $useDefaultComponent = $this->twig->getLoader()->exists($storybookComponentTemplate);
        $camelCaseArgs = json_decode($request->query->get('properties'), true);
        $componentContent = null;
        $snakeCaseArgs = [];

        foreach ($camelCaseArgs as $camelCaseKey => $value) {
            if ($camelCaseKey == 'children') {
                $componentContent = $value;

                continue;
            }

            $snakeCaseKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camelCaseKey));

            $snakeCaseArgs[$snakeCaseKey] = $value;
        }

        $context = ['args' => $snakeCaseArgs, 'content' => $componentContent, 'use_default_component' => $useDefaultComponent, 'id' => $storybookId];
        $content = $this->twig->render($previewTemplate, $context);

        // During development, storybook is served from a different port than the Symfony app
        // You can use nelmio/cors-bundle to set the Access-Control-Allow-Origin header correctly
        $headers = ['Access-Control-Allow-Origin' => 'http://localhost:6006'];

        return new Response($content, Response::HTTP_OK, $headers);
    }
}
