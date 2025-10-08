<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\DesignSystemStorybook;

use Ibexa\DesignSystemStorybook\ComponentsResolver;
use PHPUnit\Framework\TestCase;

final class ComponentsResolverTest extends TestCase
{
    private ComponentsResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ComponentsResolver();
    }

    public function testResolvesFromCustomMap(): void
    {
        self::assertSame(
            'alt_radio:input',
            $this->resolver->resolve('components/AltRadio/AltRadioInput')
        );

        self::assertSame(
            'checkbox:field',
            $this->resolver->resolve('components/Checkbox/CheckboxField')
        );
    }

    public function testFallsBackToCamelToSnake(): void
    {
        self::assertSame(
            'button',
            $this->resolver->resolve('components/Button')
        );

        self::assertSame(
            'input_text',
            $this->resolver->resolve('components/InputText')
        );
    }

    public function testIgnoresComponentsPrefix(): void
    {
        self::assertSame(
            'radio_button:input',
            $this->resolver->resolve('RadioButton/RadioButtonInput')
        );
    }
}
