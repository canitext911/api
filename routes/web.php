<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'v1', 'middleware' => 'throttle:60,1'], function () use ($router) {

    // Location
    $router->group(['namespace' => 'Location', 'prefix' => 'location', 'as' => 'location.'], function () use ($router) {
        $router->get('', ['uses' => 'LocationController@index', 'name' => 'index']);
    });

    // Lookup
    $router->group(['namespace' => 'Lookup', 'prefix' => 'lookup', 'as' => 'lookup.'], function () use ($router) {
        $router->get('', ['uses' => 'LookupController@index', 'name' => 'index']);
        $router->get('by-zip/{zip}', ['uses' => 'LookupController@byZip', 'name' => 'byZip']);
        $router->get('suggest', ['uses' => 'LookupController@suggest', 'name' => 'suggest']);
        $router->get('{id}', ['uses' => 'LookupController@show', 'name' => 'show']);
        $router->get('by-psa-id/{psaId}', ['uses' => 'LookupController@byPsaId', 'name' => 'byPsaId']);
    });
});

// Psap Indexer
$router->group([
    'namespace'  => 'PsapIndexer',
    'prefix'     => 'psap-indexer',
    'as'         => 'psapIndexer.',
    'middleware' => 'throttle:1,1'
], function () use ($router) {
    $router->get('', ['uses' => 'PsapIndexerController@index', 'name' => 'index']);
});
