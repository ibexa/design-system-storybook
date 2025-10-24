<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\DesignSystemStorybook\Preview;

final class PreviewView
{
    public function __construct(
        public readonly string $template,
        /** @var array<string,mixed> */
        public readonly array $context
    ) {
    }
}
