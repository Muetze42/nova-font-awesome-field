<?php

namespace NormanHuth\FontAwesomeField\Http;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IconController extends Controller
{
    /**
     * All configured Font Awesome styles.
     *
     * @var array
     */
    protected array $allStyles;

    /**
     * The available Font Awesome Style;
     *
     * @var array
     */
    protected array $availableStyles = [];

    /**
     * Get Font Awesome Icons for the FA-Field
     *
     * @param Request $request
     * @return array
     */
    public function icons(Request $request): array
    {
        $file = config(
            'nova-font-awesome-field.icon-file',
            base_path('vendor/norman-huth/nova-font-awesome-field/storage/icons.json')
        );
        $styles = $this->allStyles = config('nova-font-awesome-field.styles', [
            'brands',
            'duotone',
            'light',
            'regular',
            'solid',
            'thin',
        ]);

        $wantedStyles = $request->input('styles');

        if (!empty($wantedStyles) && is_array($wantedStyles)) {
            $styles = Arr::where($styles, function (string $family) use ($wantedStyles) {
                return in_array($family, $wantedStyles);
            });
        }

        $exceptedStyles = array_diff($this->allStyles, $styles);
        $excepts = [];
        if (!empty($exceptedStyles)) {
            foreach ($exceptedStyles as $exceptedStyle) {
                $excepts = array_merge($excepts, ['svg.'.$exceptedStyle]);
            }
        }

        $collection = collect(json_decode(file_get_contents($file), true))
            ->filter(function (array $item) use ($styles) {
                $this->availableStyles = array_unique(array_merge(
                    $this->availableStyles,
                    array_intersect(data_get($item, 'styles', []), $this->allStyles)
                ));
                return count(array_intersect(data_get($item, 'styles', []), $styles)) > 0;
            })->map(function (array $item) use ($excepts) {
                return Arr::except($item, $excepts);
            });

        if ($search = $request->input('search')) {
            $search = Str::lower($search);
            $collection = $collection->filter(function (array $item, string $key) use ($search) {
                $searchIn = $key;
                $searchIn.= implode(' ', data_get($item ,'search.terms'));
                return str_contains(Str::lower($searchIn), Str::lower($search));
            });
        }

        $array = $collection->map(function (array $item) {
            return Arr::except($item, ['search', 'styles']);
        })->toArray();

        $chunk = (int) $request->input('chunk');
        $chunks = array_chunk($array, config('nova-font-awesome-field.chunk', 86), true);
        $next = $chunk+1;

        return [
            'icons' => $chunks[$chunk] ?? [],
            'chunk' => !empty($chunks[$next]) ? $next : null,
            'availableStyles' => $this->availableStyles,
        ];
    }
}
