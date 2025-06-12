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
    
    public function getPreview(Request $request, string $storybookId): Response
    {
        $template = sprintf('@IbexaDesignSystemStorybook/themes/standard/storybook/preview.html.twig');
        $camelCaseArgs = json_decode($request->query->get('properties'), true);
        $snakeCaseArgs = [];

        foreach ($camelCaseArgs as $camelCaseKey => $value) {
            $snakeCaseKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camelCaseKey));

            $snakeCaseArgs[$snakeCaseKey] = $value;
        }

        $context = ['args' => $snakeCaseArgs, 'id' => $storybookId];
        $content = $this->twig->render($template, $context);

        // During development, storybook is served from a different port than the Symfony app
        // You can use nelmio/cors-bundle to set the Access-Control-Allow-Origin header correctly
        $headers = ['Access-Control-Allow-Origin' => 'http://localhost:6006'];

        return new Response($content, Response::HTTP_OK, $headers);
    }
}
