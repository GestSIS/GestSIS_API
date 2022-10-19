<?php

return [

    /*
     * You can enable CORS for 1 or multiple paths.
     * Example: ['api/*']
     */
    'config' => env('PDFTK_BIN_PATH') ? array(
        // e.g. /project/pdftk/bin/pdftk
        'command' => env('PDFTK_BIN_PATH'),
        'procEnv' => array(
            // e.g. /project/pdftk/bin (should contain libgcj.so.10)
            'LD_LIBRARY_PATH' => config('PDFTK_LIB_FOLDER')
        ),
    ) : [],
];
