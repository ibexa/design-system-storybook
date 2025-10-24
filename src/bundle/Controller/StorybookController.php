<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\DesignSystemStorybook\Controller;

use Ibexa\DesignSystemStorybook\Preview\PreviewRendererInterface;
use Ibexa\DesignSystemStorybook\Preview\PreviewViewBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class StorybookController
{
    public function __construct(
        private readonly PreviewViewBuilder $viewBuilder,
        private readonly PreviewRendererInterface $renderer
    ) {
    }

    public function getStatus(): Response
    {
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function getPreview(Request $request, string $storybookId): Response
    {
        $view = $this->viewBuilder->build($request, $storybookId);

        $content = $this->renderer->render($view);

        $headers = ['Access-Control-Allow-Origin' => 'http://localhost:6006'];

        return new Response($content, Response::HTTP_OK, $headers);
    }
}
