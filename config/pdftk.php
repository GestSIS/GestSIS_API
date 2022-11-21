<?php

return [

    /*
     * Configuration de PDFTK
     */
    'config' => env('PDFTK_BIN_PATH') ? array(
        // e.g. /project/pdftk/bin/pdftk
        'command' => env('PDFTK_BIN_PATH'),
        'procEnv' => array(
            // e.g. /project/pdftk/bin (should contain libgcj.so.10)
            'LD_LIBRARY_PATH' => env('PDFTK_LIB_FOLDER')
        ),
    ) : [],
];
