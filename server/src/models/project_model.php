<?php
require_once 'basic_model.php';

class Project {
    public $number;
    public $user_id;
    public $country;
    public $region;

}

class ProjectModel extends BasicModel {

    function __construct($db)
    {
        parent::__construct($db);
        $this->tableName = 'projects';
        $this->fields = array(
            'id',
            'number',
            'created_on',
            'modified_on',
            'region',
            'country',
            'project',
            'chassis',
            'project_id',
        );
    }

    public static function getRegions() {
        $json_file = __DIR__ . '/regions.json';

        $str = file_get_contents($json_file);
        $regions = json_decode($str, true);
        return $regions;
    }

    public function findProject($project, $chassis, $projectId) {
        $result = $this->db->projects()->where(array(
            "project" => $project,
            "chassis" => $chassis,
            "project_id" => $projectId,
            ));

        if ($project = $result->fetch()) {
            return $this->entity($project);
        } else {
            return false;
        }
    }
}