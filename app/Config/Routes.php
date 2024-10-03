<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');


/* RUTAS API */
$routes->group('api', function ($routes) {

    // $routes->get('/', 'Api::index');
    $routes->match(['get', 'post'], 'index', 'Api::index');
    $routes->match(['get', 'post'], 'incripcion', 'Api::incripcion');

});

$routes->group('newPDF', function ($routes) {
    $routes->match(['get', 'post'], 'newpdf', 'newPDF::newpdf');

});
