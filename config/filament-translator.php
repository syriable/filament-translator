<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Required translation attributes
    |--------------------------------------------------------------------------
    |
    | Override which component attributes must have a translation. Keys are the
    | attribute (method) name — for example "label", "placeholder", "tooltip",
    | "heading" — and the value decides whether it is required:
    |
    |   true  => required (no fallback to null; surfaces a missing key, and is
    |            eligible for automatic key creation)
    |   false => optional (falls back to null when no translation exists)
    |
    | Any attribute not listed keeps the package default (only the primary
    | "label"/"heading"/"content"-style attributes are required by default).
    | Overrides apply across every context where the attribute appears
    | (forms, tables, columns, filters, actions, summarizers).
    |
    */

    'required' => [
        // 'placeholder' => true,
        // 'tooltip' => true,
        // 'label' => false,
    ],

];
