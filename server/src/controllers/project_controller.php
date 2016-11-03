<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once 'basic_controller.php';
require_once 'http_status_codes.php';
require_once __DIR__ . '/../models/project_model.php';

class ProjectController extends BasicController
{
    private $model;

    function __construct(\Interop\Container\ContainerInterface $ci)
    {
        parent::__construct($ci);
        $this->model = new ProjectModel($this->db);
    }

    public function getAll(Request $request, Response $response, $args) {
        $projects = $this->model->getAll();
        return $this->apiResponse($response, $projects);
    }

    public function link(Request $request, Response $response, $args) {
        $project = $request->getParsedBody();

        $result = $this->model->findProject($project['project'], $project['chassis'], $project['project_id']);
        if ($result == null) {

            // add new project
            $project['created_on'] = date('Y-m-d H:i:s');
            $project['modified_on'] = date('Y-m-d H:i:s');

            $result = $this->model->insert($project);
        } else if ($result['number'] != $project['number']) {
            return $this->apiResponse($response, SNAPSHOT_PARAM_ERROR, HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        return $this->apiResponse($response, $result);
    }

    public function find(Request $request, Response $response, $args) {
        $q = $request->getQueryParams();

        $result = $this->model->findProject($q['project'], $q['chassis'], $q['project_id']);
        if ($result == null) {
            $result = [];
        }

        return $this->apiResponse($response, $result);
    }

    public function delete(Request $request, Response $response, $args) {
        $id = $args['id'];

        $project = $this->model->get($id);
        if (!$project) {
            return $this->apiResponse($response, 'Invalid Param', HttpStatusCodes::HTTP_NOT_FOUND);
        }

        $result = $this->model->delete($project['id']);
        if (!$result) {
            return $this->apiResponse($response, 'Database error', HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse($response, $project);
    }
}