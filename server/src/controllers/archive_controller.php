<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once 'basic_controller.php';
require_once 'http_status_codes.php';
require_once __DIR__ . '/../models/archive_model.php';
require_once __DIR__ . '/../models/project_model.php';
require_once __DIR__ . '/../models/snapshot_model.php';

class ArchiveController extends BasicController {
    private $model;

    function __construct(\Interop\Container\ContainerInterface $ci)
    {
        parent::__construct($ci);
        $this->model = new ArchiveModel($this->db);
    }

    function linkedProject($archive) {
        $project = null;
        if (isset($archive['project_number'])) {
            $pm = new ProjectModel($this->db);
            $project = $pm->find('number', $archive['project_number']);
            if (!$project) {
                $project = null;
            }
        }

        return $project;
    }

    public function getAll(Request $request, Response $response, $args) {
        $archives = $this->model->getAll();

        foreach($archives as &$archive) {
            $archive['_project'] = $this->linkedProject($archive);
        }

        return $this->apiResponse($response, $archives);
    }

    public function getList(Request $request, Response $response, $args) {
        if ($this->token->role == 'admin') {
            $archives = $this->model->getAll();
        } else {
            $archives = $this->model->getList($this->token->id);
        }

        foreach($archives as &$archive) {
            $archive['_project'] = $this->linkedProject($archive);;
        }
        return $this->apiResponse($response, $archives);
    }

    public function get(Request $request, Response $response, $args) {
        $uuid = $args['uuid'];

        $archive = $this->model->getByUUID($uuid);
        if ($archive == null) {
            return $this->apiResponse($response, SNAPSHOT_PARAM_ERROR, HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        $archive['_project'] = $this->linkedProject($archive);;

        return $this->apiResponse($response, $archive);
    }

    public function delete(Request $request, Response $response, $args) {
        $uuid = $args['uuid'];

        $archive = $this->model->getByUUID($uuid);
        if ($archive == null) {
            return $this->apiResponse($response, SNAPSHOT_PARAM_ERROR, HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        $result = $this->model->delete($archive['id']);
        if (!$result) {
            return $this->apiResponse($response, SNAPSHOT_PARAM_ERROR, HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        }

        // remove the file
        $filePath = $this->settings['archivePath'].$archive['file_name'];
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                $this->logger->debug('could not delete the file ' . $filePath);
            };
        }

        return $this->apiResponse($response, $archive);
    }

    public function update(Request $request, Response $response, $args) {
        $_project = null;

        $archive = $request->getParsedBody();
        if (isset($archive['_project'])) {
            $project = $archive['_project'];
            $projectModel = new ProjectModel($this->db);

            if ($project['id'] == '') {
                $project['created_on'] = date('Y-m-d H:i:s');
                $project['modified_on'] = date('Y-m-d H:i:s');
                $_project = $projectModel->insert($project);
            } else {
                $_project = $project;
            }

            $this->logger->debug('_project: '.json_encode($_project));
        }

        unset($archive['_project']);

        if (!$this->model->update($archive)) {
            return $this->apiResponse($response, 'Update of archive failed', HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        }

        $archive['_project'] = $_project;

        $this->logger->debug('archive: '.json_encode($archive));

        return $this->apiResponse($response, $archive);
    }
}
