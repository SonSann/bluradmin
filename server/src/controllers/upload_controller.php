<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once 'basic_controller.php';

require_once __DIR__ . '/../models/archive_model.php';
require_once __DIR__ . '/../models/project_model.php';
require_once __DIR__ . '/../models/tag_model.php';
require_once __DIR__ . '/../models/bucket_model.php';
require_once __DIR__ . '/../parser/archive_parser.php';

define('GV_PROJECT', '@GV.Project');
define('GV_CHASSIS', '@GV.ProjectInfo.Chassis');
define('GV_PROJECT_ID', '@GV.ProjectInfo.Project.Project_ID');


class UploadController extends BasicController {
    private $archivePath;
    private $tempPath;

    function __construct(\Interop\Container\ContainerInterface $ci)
    {
        parent::__construct($ci);

        $this->archivePath = $this->settings['archivePath'];
        $this->tempPath = $this->settings['tempPath'];
    }

    public function upload(Request $request, Response $response, $args) {
        $archiveModel = new ArchiveModel($this->db);

        $files = $request->getUploadedFiles();
        $file = $files['file'];
        $file_name = $file->getClientFileName();

        // TODO: check if the file with same name already uploaded.
        $result = $archiveModel->find('file_name', $file_name);
        if ($result) {
            return $this->apiResponse($response, SNAPSHOT_ARCHIVE_EXISTS, HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        if ($file->getError() === UPLOAD_ERR_OK) {
            $target_name = $this->archivePath.$file_name;
            $file->moveTo($target_name);
        } else {
            return $this->apiResponse($response,
                SNAPSHOT_UPLOAD_ERROR ." ".$file->getError(),
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // create archive instance
        $archive = $archiveModel->entity();
        $archive['user_id'] = $this->user['id'];
        $archive['user_uuid'] = $this->user['uuid'];
        $archive['file_name'] = $file->getClientFileName();
        $archive['uuid'] = uniqid();
        $archive['uploaded_on'] = date('Y-m-d H:i:s');

        // parse archive
        $result = $this->parseArchive($archive);
        if ($result['code'] != 'success') {
            // mark parse flag
            $archive['status'] = $result['status'];
            $archive['parse_flag'] = 2;
        } else {
            $snapshot = $result['snapshot'];

            $snapshotModel = new SnapshotModel();

            $snapshotModel->replace($snapshot);

            $archive['project'] = $snapshotModel->findTag($snapshot, GV_PROJECT);
            $archive['chassis'] = $snapshotModel->findTag($snapshot, GV_CHASSIS);;
            $archive['project_id'] = $snapshotModel->findTag($snapshot, GV_PROJECT_ID);
            $archive['parse_flag'] = 0;
        }

        // check project
        $projectModel = new ProjectModel($this->db);
        $project = $projectModel->findProject($archive['project'], $archive['chassis'], $archive['project_id']);
        if (!$project) {
            $archive['status'] = 'NOT ASSIGN';
            $project = $projectModel->entity();

            $project['project'] = $archive['project'];
            $project['chassis'] = $archive['chassis'];
            $project['project_id'] = $archive['project_id'];
        }

        $result = $archiveModel->insert($archive);
        if ($result == null) {
            return $this->apiResponse($response, SNAPSHOT_DB_ERROR, HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        }

        // upload the archive to AWS S3
        $bucketModel = new BucketModel($this->db, $this->archivePath);
        if ($bucketModel->uploadFile($archive)) {

        } else {

        }

        $archive['_project'] = $project;
        $archive['id'] = $result['id'];
        return $this->apiResponse($response, $archive);
    }

    function parseArchive($archive) {
        $workPath = $this->tempPath . $archive['uuid'] . "/";
        if (!file_exists($workPath)) {
            mkdir($workPath, 0777, true);
        }

        $tags = (new TagModel($this->db))->getAll();
        $parser = new ArchiveParser($this->archivePath, $this->logger);
        return $parser->parseArchive($archive, $workPath, $tags);
    }

    public function regions(Request $request, Response $response, $args) {
        return $this->apiResponse($response, ProjectModel::getRegions());
    }
}