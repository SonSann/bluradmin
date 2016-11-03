<?php

require_once 'basic_model.php';

class TagModel extends BasicModel {

    function __construct($db)
    {
        parent::__construct($db);
        $this->tableName = 'tags';
        $this->fields = array(
            'id',
            'file_name',
            'fields'
        );
    }

    public function populate_fields($tag) {
        $tag['field_list'] = json_decode($tag['fields']);
        return $tag;
    }

    public function entity($result = null) {
        return $this->populate_fields(parent::entity($result));
    }

    public function delete($tag) {
        $sql = "DELETE FROM tags WHERE id = ?;";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $tag['id']);
        $stmt->execute();

        return $this->populate_fields($tag);
    }
}
