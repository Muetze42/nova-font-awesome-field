<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Font Awesome icons.json
    |--------------------------------------------------------------------------
    |
    | This value specifies the location of the Font Awesome icon.js file to be used.
    | to be used.
    | This file can be reduced with the command `nova-fa-field:shrink-icon-file`.
    |
    */

    'icon-file' => base_path('vendor/norman-huth/nova-font-awesome-field/storage/icons.json'),

    /*
    |--------------------------------------------------------------------------
    | Font Awesome Default Families
    |--------------------------------------------------------------------------
    |
    | Here you specify which families are to be available for the field
    | without further details.
    | (Unavailable families are not displayed in the field.)
    |
    */

    'default-styles' => [
        'brands',
        'duotone',
        'light',
        'regular',
        'solid',
        'thin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Save Raw SVG
    |--------------------------------------------------------------------------
    |
    | Choose whether to save an icon as SVG or the Font Awesome stylesheet
    | classes.
    | If you change this you have to update the icons in the database.
    |
    */

    'save-raw-svg' => true,
];
