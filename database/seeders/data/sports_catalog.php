<?php

$sportsDirectory = __DIR__.'/sports';

return collect(glob($sportsDirectory.'/*/exercises.php'))
    ->sort()
    ->map(fn (string $path) => require $path)
    ->values()
    ->all();
