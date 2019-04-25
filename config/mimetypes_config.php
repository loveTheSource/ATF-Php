<?php

/**
 * supported mime types config
 * 
 * grouped into: 
 * - image
 * - document
 * - audio
 * - video
 * - archive
 * 
 * 
 * the final return value will look like this:
 * 
 * return [
 *  'extension_mimetype' => ...         // extension => mimetyp (grouped)
 *  'extension_mimetype_all' => ...     // extension => mimetyp (flat)
 *  'mimetype_extension' => ...         // mimetyp => extension (grouped)
 *  'mimetype_extension_all' => ...     // mimetyp => extension (flat)
 *  'extensions_map' => ...             // extension => mimetyp (grouped)
 * ];
 * 
 */


// defining local subgroups
$mimetypes = [
    'image' => [
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'png' => 'image/png'
    ],
    'document' => [
        'pdf' => 'application/pdf',
        'txt' => 'text/plain',
        'rtf' => 'text/rtf',
    ],
    'video' => [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'video/ogg'
    ],
    'audio' => [
        'mp3' => 'audio/mpeg',
        'webm' => 'audio/webm',
        'ogg' => 'audio/ogg',
        'wav' => 'audio/wav'
    ],
    'archive' => [
        'zip' => 'application/zip',
        'gz' => 'application/gzip'
    ]
];


// config 
$mimetypesConfig = [];


// extenion => mimetype
$mimetypesConfig['extension_mimetype'] = $mimetypes;
$mimetypesConfig['extension_mimetype_all'] = array_merge(
    $mimetypes['image'], 
    $mimetypes['document'], 
    $mimetypes['audio'], 
    $mimetypes['video'], 
    $mimetypes['archive']
);


// mimetype => extension
$mimetypesConfig['mimetype_extension'] = [
    'image' => array_flip($mimetypes['image']), 
    'document' => array_flip($mimetypes['document']), 
    'audio' => array_flip($mimetypes['audio']), 
    'video' => array_flip($mimetypes['video']), 
    'archive' => array_flip($mimetypes['archive'])
];
$mimetypesConfig['mimetype_extension_all'] = array_merge(
    array_flip($mimetypes['image']), 
    array_flip($mimetypes['document']), 
    array_flip($mimetypes['audio']), 
    array_flip($mimetypes['video']), 
    array_flip($mimetypes['archive'])
);


// extensions map (duplicate extensions)
$mimetypesConfig['extensions_map'] = [
    'image' => [
        'jpeg' => 'jpg',
        'jpe' => 'jpg',
        'tif' => 'tiff',    
    ],
    'video' => [
        'ogv' => 'ogg'
    ]
];



return $mimetypesConfig;