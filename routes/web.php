<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'v1', 'middleware' => ['throttle:60,1', 'cors']], function () use ($router) {

    // Location
    $router->group(['namespace' => 'Location', 'prefix' => 'location', 'as' => 'location.'], function () use ($router) {
        $router->get('', ['uses' => 'LocationController@index', 'name' => 'index']);
    });

    // Lookup
    $router->group(['namespace' => 'Lookup', 'prefix' => 'lookup', 'as' => 'lookup.'], function () use ($router) {
        $router->get('', ['uses' => 'LookupController@index', 'name' => 'index']);

        $router->get('by-zip/{zip}', ['uses' => 'LookupController@byZip', 'name' => 'byZip']);
        $router->get('by-psa-id/{psaId}', ['uses' => 'LookupController@byPsaId', 'name' => 'byPsaId']);

        $router->get('{id}', ['uses' => 'LookupController@show', 'name' => 'show']);
    });

    // Nearby
    $router->group(['namespace' => 'Nearby', 'prefix' => 'nearby', 'as' => 'nearby.'], function () use ($router) {
        $router->get('', ['uses' => 'NearbyController@index', 'name' => 'index']);
    });

    // Recent
    $router->group(['namespace' => 'Recent', 'prefix' => 'recent', 'as' => 'recent.'], function () use ($router) {
        $router->get('', ['uses' => 'RecentController@index', 'name' => 'index']);
    });

    // Suggest
    $router->group(['namespace' => 'Suggest', 'prefix' => 'suggest', 'as' => 'suggest.'], function () use ($router) {
        $router->get('', ['uses' => 'SuggestController@index', 'name' => 'index']);
    });
});

// Psap Indexer
$router->group([
    'namespace'  => 'PsapIndexer',
    'prefix'     => 'psap-indexer',
    'as'         => 'psapIndexer.',
    'middleware' => 'throttle:1,60'
], function () use ($router) {
    $router->get('', ['uses' => 'PsapIndexerController@index', 'name' => 'index']);
});
