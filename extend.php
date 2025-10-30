<?php

namespace Olleksi\Homepage;

use Flarum\Extend;

return [
    (new Extend\Routes('forum'))
        ->get('/', 'homepage', Controller\HomepageController::class),
];
