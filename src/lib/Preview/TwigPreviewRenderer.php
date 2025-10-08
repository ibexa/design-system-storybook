<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\DesignSystemStorybook\Preview;

use Twig\Environment;

final readonly class TwigPreviewRenderer implements PreviewRendererInterface
{
    public function __construct(private Environment $twig)
    {
    }

    public function render(PreviewView $view): string
    {
        return $this->twig->render($view->template, $view->context);
    }
}
