protected $middlewareGroups = [
    'web' => [
        // ... autres middlewares
        \App\Http\Middleware\SetLocale::class,
    ],
];