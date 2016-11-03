<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once 'basic_controller.php';
require_once 'http_status_codes.php';
require_once __DIR__ . '/../models/tag_model.php';

class TagController extends BasicController {
    private $model;

    function __construct(\Interop\Container\ContainerInterface $ci)
    {
        parent::__construct($ci);
        $this->model = new TagModel($this->db);
    }

    public function getAll(Request $request, Response $response, $args) {
        return $this->apiResponse($response, $this->model->getAll());
    }

    public function get(Request $request, Response $response, $args) {
        $id = $args['id'];

        $tag = $this->model->get($id);
        if ($tag == null) {
            return $this->apiResponse($response, SNAPSHOT_PARAM_ERROR, HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        $response->getBody()->write(json_encode($tag));
        return $response->withHeader('Content-type', 'application/json');
    }

    public function insert(Request $request, Response $response, $args) {
        $tag = $request->getParsedBody();
        $tag = $this->model->insert($tag);
        if ($tag == null) {
            return $this->apiResponse($response, SNAPSHOT_DB_ERROR, HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->apiResponse($response, $tag);
    }

    public function delete(Request $request, Response $response, $args) {
        $id = $args['id'];

        $tag = $this->model->get($id);
        if ($tag == null) {
            return $this->apiResponse($response, SNAPSHOT_PARAM_ERROR, HttpStatusCodes::HTTP_BAD_REQUEST);
        }
        $this->model->delete($tag);
        return $this->apiResponse($response, $tag);
    }

    public function update(Request $request, Response $response, $args) {
        $tag = $request->getParsedBody();
        $tag = $this->model->update($tag);
        return $this->apiResponse($response, $tag);
    }
}
