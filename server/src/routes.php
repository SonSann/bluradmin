<?php

require_once 'controllers/archive_controller.php';
require_once 'controllers/snapshot_controller.php';
require_once 'controllers/things_controller.php';
require_once 'controllers/upload_controller.php';
require_once 'controllers/project_controller.php';
require_once 'controllers/tag_controller.php';
require_once 'controllers/statistics_controller.php';
require_once 'controllers/auth_controller.php';

$app->get('/', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    if (isset($_SESSION['user'])) {
        // Render index view
        return $this->renderer->render($response, 'index.html', $args);
    } else {
        return $this->renderer->render($response, 'auth.html', $args);
    }
});

$app->post('/login', '\AuthController:login');

$app->group('/api/', function() use ($app) {

    $app->group('auth', function() use ($app) {
        $app->post('/basic', '\AuthController:basic');
        $app->post('/local', '\AuthController:local');
    });

    $app->group('users', function() use ($app) {
        $app->get('/me', '\AuthController:me');
    });

    $app->group('things', function() use ($app) {
        $app->get('', '\ThingsController:index');
        $app->get('/{id}', '\ThingsController:show');
        $app->post('', '\ThingsController:create');
        $app->put('/{id}', '\ThingsController:update');
        $app->patch('/{id}', '\ThingsController:update');
        $app->delete('/{id}', '\ThingsController:destroy');
    });

    $app->group('upload', function() use ($app) {
        $app->post('', '\UploadController:upload');
    });

    $app->group('projects', function() use ($app) {

        $app->get('', '\ProjectController:getAll');
        $app->post('', '\ProjectController:link');
        $app->get('/find', '\ProjectController:find');
        $app->delete('/{id}', '\ProjectController:delete');
    });

    $app->group('archives', function() use ($app) {

        $app->get('', '\ArchiveController:getList');
        $app->post('', '\ArchiveController:upload');
        $app->get('/all', '\ArchiveController:getAll');
        $app->get('/{uuid}', '\ArchiveController:get');
        $app->delete('/{uuid}', '\ArchiveController:delete');
        $app->put('', '\ArchiveController:update');

    });

    $app->group('snapshots', function() use ($app) {

        $app->get('', '\SnapshotController:getList');
        $app->get('/all', '\SnapshotController:getAll');
        $app->get('/{id}', '\SnapshotController:get');
        $app->get('/file/{id}', '\SnapshotController:getByFileId');
        $app->get('/parse/{snapshotId}', '\SnapshotController:parse');
        $app->delete('/', '\SnapshotController:delete');
        $app->put('/', '\SnapshotController:update');
        $app->get('/page/{start}/{limit}', '\SnapshotController:getPage');
    });

    $app->group('tags', function() use ($app) {

        $app->get('', '\TagController:getAll');
        $app->post('', '\TagController:insert');
        $app->put('/{id}', '\TagController:update');
        $app->delete('/{id}', '\TagController:delete');
    });

    $app->get('regions', '\UploadController:regions');

    $app->group('statistics', function() use ($app) {
        $app->get('/region/{regionId}', '\StatisticsController:byRegion');
        $app->get('/country/{countryId}', '\StatisticsController:byCountry');
        $app->get('/user/{userId}', '\StatisticsController:byUser');
    });

    $app->group('users', function() use ($app) {
        $app->get('', '\StatisticsController:byRegion');
    });
});

