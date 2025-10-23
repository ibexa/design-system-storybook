<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\DesignSystemStorybook;

final class ComponentsResolver
{
    /** @var array<string, string> */
    private const array CUSTOM_COMPONENTS_MAP = [
        'AltRadio/AltRadioInput' => 'alt_radio:input',
        'AltRadio/AltRadiosListField' => 'alt_radio:list_field',
        'Checkbox/CheckboxField' => 'checkbox:field',
        'Checkbox/CheckboxInput' => 'checkbox:input',
        'Checkbox/CheckboxesListField' => 'checkbox:list_field',
        'Dropdown/DropdownMultiInput' => 'dropdown_multi:input',
        'Dropdown/DropdownSingleInput' => 'dropdown_single:input',
        'InputText/InputTextField' => 'input_text:field',
        'InputText/InputTextInput' => 'input_text:input',
        'RadioButton/RadioButtonField' => 'radio_button:field',
        'RadioButton/RadioButtonInput' => 'radio_button:input',
        'RadioButton/RadioButtonsListField' => 'radio_button:list_field',
        'ToggleButton/ToggleButtonField' => 'toggle_button:field',
        'ToggleButton/ToggleButtonInput' => 'toggle_button:input',
    ];

    public function resolve(string $storybookId): string
    {
        $clean = str_replace('components/', '', $storybookId);

        return self::CUSTOM_COMPONENTS_MAP[$clean] ?? $this->camelToSnake($clean);
    }

    private function camelToSnake(string $s): string
    {
        $snake = preg_replace('/(?<=\w)(?=[A-Z])|(?<=[a-z])(?=[0-9])/', '_', $s) ?? '';

        return strtolower($snake);
    }
}
