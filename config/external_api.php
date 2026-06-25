<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External API matching threshold
    |--------------------------------------------------------------------------
    |
    | When payload matching is less than or equal to this value, the request is
    | treated as incomplete and the sender receives a missing-details email.
    | Default 0 = only matching:0 triggers the incomplete-travel flow.
    |
    */
    'matching_threshold' => (int) env('EXTERNAL_API_MATCHING_THRESHOLD', 0),

];
