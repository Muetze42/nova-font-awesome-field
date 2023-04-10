<?php

namespace NormanHuth\FontAwesomeField\Http;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IconController extends Controller
{
    /**
     * Get Font Awesome Icons for the FA-Field
     *
     * @param Request $request
     * @return array
     */
    public function icons(Request $request): array
    {
        $file = config('nova-font-awesome-field.icon-file');
        $styles = $allStyles = config('nova-font-awesome-field.default-styles');

        $wantedStyles = $request->input('styles');

        if ($wantedStyles && is_array($wantedStyles)) {
            $styles = Arr::where($styles, function (string $family) use ($wantedStyles) {
                return in_array($family, $wantedStyles);
            });
        }

        $exceptedStyles = array_diff($allStyles, $styles);
        $excepts = [];
        if (!empty($exceptedStyles)) {
            foreach ($exceptedStyles as $exceptedStyle) {
                $excepts = array_merge($excepts, ['svg.'.$exceptedStyle]);
            }
        }

        $collection = collect(json_decode(file_get_contents($file), true))
            ->filter(function (array $item) use ($styles) {
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
        $chunks = array_chunk($array, 90, true);
        $next = $chunk+1;

        return [
            'icons' => $chunks[$chunk] ?? [],
            'chunk' => !empty($chunks[$next]) ? $next : null,
        ];
    }
}
