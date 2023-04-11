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
     * Style Selector available.
     *
     * @var bool
     */
    protected bool $styleSelector;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'font-awesome-field';

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        $this->saveRawSVG = (bool) config('nova-font-awesome-field.save-raw-svg', true);
        $this->styleSelector = (bool) config('nova-font-awesome-field.style-selector', true);
        $this->texts = [
            'header' => __('Edit Icon'),
            'cancel' => __('Cancel'),
            'update' => __('Update'),
            'search' => __('Search'),
            'remove' => __('Remove Icon'),
            'styles' => __('Styles'),
            'more'   => __('Load more'),
            'null'   => __('No Icons Found'),
        ];
        parent::__construct($name, $attribute, $resolveCallback);
    }

    /**
     * Save Icon as raw SVG option.
     *
     * @var bool
     */
    protected bool $saveRawSVG;

    /**
     * @param string $key
     * @param string $text
     * @return $this
     */
    public function setText(
        #[ExpectedValues(values: ['header', 'cancel', 'update', 'search', 'remove', 'more', '404'])]
        string $key,
        string $text
    ): static
    {
        $this->texts[$key] = $text;

        return $this;
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
     * Show Style Selector.
     *
     * @return $this
     */
    public function showStyleSelector(): static
    {
        $this->styleSelector = true;

        return $this;
    }

    /**
     * Hide Style Selector.
     *
     * @return $this
     */
    public function hideStyleSelector(): static
    {
        $this->styleSelector = false;

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
                $file = config(
                    'nova-font-awesome-field.icon-file',
                    base_path('vendor/norman-huth/nova-font-awesome-field/storage/icons.json')
                );
                $contents = json_decode(file_get_contents($file), true);
                $currentSVG = (string) data_get($contents, $icon.'.svg.'.$style.'.raw', '');
            } else {
                $currentSVG = '';
            }
        } else {
            $currentSVG = $this->value;
        }

        $this->withMeta([
            'currentSVG'    => $currentSVG,
            'saveRawSVG'    => $this->saveRawSVG,
            'asHtml'        => true,
            'texts'         => $this->texts,
            'styleSelector' => $this->styleSelector,
        ]);

        return parent::jsonSerialize();
    }
}
