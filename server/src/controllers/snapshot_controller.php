<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once 'basic_controller.php';
require_once 'http_status_codes.php';
require_once __DIR__ . '/../models/snapshot_model.php';
require_once __DIR__ . '/../models/project_model.php';

class SnapshotController extends BasicController {
    private $model;

    function __construct(\Interop\Container\ContainerInterface $ci)
    {
        parent::__construct($ci);
        $this->model = new SnapshotModel();
    }

    public function getList(Request $request, Response $response, $args) {
        $user_id = '';
        return $this->apiResponse($response, $this->model->getList($user_id));
    }

    public function getAll(Request $request, Response $response, $args) {
        return $this->apiResponse($response, $this->model->getAll());
    }

    public function getPage(Request $request, Response $response, $args)
    {
        $filter = array();

        $query = $request->getQueryParams();
        foreach(['archive', 'path', 'file', 'tag', 'val'] as $f ) {
            if (isset($query[$f]))
                $filter[$f] = new \MongoDB\BSON\Regex($query[$f], 'i');
        }

        $start = (int)$args['start'];
        $limit = (int)$args['limit'];

        $options = ['skip' => $start, 'limit' => $limit ];

        return $this->apiResponse($response, $this->model->find($filter, $options));
    }
}
