<?php

namespace NormanHuth\FontAwesomeField;

use JetBrains\PhpStorm\ExpectedValues;
use Laravel\Nova\Fields\Field;

class FontAwesome extends Field
{
    /**
     * The Component Texts.
     *
     * @var array
     */
    protected array $texts;

    /**
     * Save Icon as raw SVG option.
     *
     * @var bool
     */
    protected bool $saveRawSVG;

    public function changeTranslation(
        #[ExpectedValues(values: ['header', 'cancel', 'update', 'search', 'remove', 'more', '404'])]
        string $key,
        string $text
    ): static
    {
        $this->texts[$key] = $text;

        return $this;
    }

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'font-awesome-field';

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        $this->saveRawSVG = (bool) config('nova-font-awesome-field.save-raw-svg', true);
        $this->texts = [
            'header' => __('Edit Icon'),
            'cancel' => __('Cancel'),
            'update' => __('Update'),
            'search' => __('Search'),
            'remove' => __('Remove Icon'),
            'more'   => __('Load more'),
            '404'    => __('No Icons Found'),
        ];
        parent::__construct($name, $attribute, $resolveCallback);
    }

    /**
     * Save raw SVG icon.
     *
     * @return $this
     */
    public function saveRawSVG(): static
    {
        $this->saveRawSVG = true;

        return $this;
    }

    /**
     * Save Font Awesome Icon class.
     *
     * @return $this
     */
    public function saveIonClaas(): static
    {
        $this->saveRawSVG = false;

        return $this;
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        if ($this->value && !str_contains($this->value, '<svg')) {
            $parts = explode(' ', $this->value);
            $style = str_replace('fa-', '', $parts[0]);
            $icon = !empty($parts[1]) ? str_replace('fa-', '', $parts[1]) : null;
            if ($icon) {
                $file = config('nova-font-awesome-field.icon-file');
                $contents = json_decode(file_get_contents($file), true);
                $currentSVG = (string) data_get($contents, $icon.'.svg.'.$style.'.raw', '');
            } else {
                $currentSVG = '';
            }
        } else {
            $currentSVG = $this->value;
        }

        $this->withMeta([
            'currentSVG' => $currentSVG,
            'saveRawSVG' => $this->saveRawSVG,
            'asHtml'     => true,
            'texts'      => $this->texts,
        ]);

        return parent::jsonSerialize();
    }
}
