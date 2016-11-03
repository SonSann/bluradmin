<?php
require_once 'basic_model.php';
require_once 'project_model.php';

class ArchiveModel extends BasicModel {

    function __construct($db)
    {
        parent::__construct($db);
        $this->tableName = 'archives';
        $this->fields = array(
            'id',
            'file_name',
            'uuid',
            'user_id',
            'user_name',
            'uploaded_on',
            'project',
            'chassis',
            'project_id',
            'project_number',
            'status',
            'parse_flag',
        );
    }

    public function getList($user_id) {
        $archives = array();

        $result = $this->db->archives()->where('user_id', $user_id)->order('uploaded_on DESC');
        foreach ($result as $archive) {
            $archives[] = $this->entity($archive);
        }

        return $archives;
    }

    public function getListByUUID($user_uuid) {
        $archives = array();

        $result = $this->db->archives()->where('user_uuid', $user_uuid)->order('uploaded_on DESC');
        foreach ($result as $archive) {
            $archives[] = $this->entity($archive);
        }

        return $archives;
    }
}