<?php
require_once 'basic_controller.php';
require_once 'statistics_controller.php';

class StatisticsController extends BasicController {

    public function byRegion($request, $response, $args) {
        $regionId = $args['regionId'];
    }

    public function byCountry($request, $response, $args) {
        $countryId = $args['countryId'];
    }

    public function byUser($request, $response, $args) {
        $countryId = $args['countryId'];
    }
}