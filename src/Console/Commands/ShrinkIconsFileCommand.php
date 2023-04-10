<?php

namespace NormanHuth\FontAwesomeField\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class ShrinkIconsFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova-fa-field:shrink-icon-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove unnecessary elements from a Font Awesome Icon JSON file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = config('nova-font-awesome-field.icon-file');
        $collection = collect(json_decode(file_get_contents($file), true))
            ->map(function (array $item) {
                $excepts = [
                    'aliases',
                    'changes',
                    'label',
                    'ligatures',
                    'unicode',
                    'voted',
                    'free',
                ];

                $styles = [
                    'solid',
                    'regular',
                    'light',
                    'thin',
                    'duotone',
                    'brands',
                ];

                foreach ($styles as $style) {
                    $excepts = array_merge($excepts, [
                        'svg.'.$style.'.last_modified',
                        'svg.'.$style.'.viewBox',
                        'svg.'.$style.'.width',
                        'svg.'.$style.'.height',
                        'svg.'.$style.'.path',
                    ]);
                }

                return Arr::except($item, $excepts);
            });

        file_put_contents($file, json_encode($collection->toArray(),JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        //file_put_contents($file, json_encode($collection->toArray()));
    }
}
