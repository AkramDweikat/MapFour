<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Meta_values_M extends CI_Model {

    var $story_id   = '';
    var $meta_type   = '';
    var $meta_value   = '';

    public function __construct() {
        parent::__construct();
    }
    
    public function findByStoryId($storyId) {
        $query = $this->db->get_where('meta_values', array('story_id' => $storyId));
        return $query->result();
    }

    public function insert($storyId, $type, $value) {
        $this->story_id   = $storyId;
        $this->meta_type   = $type;
        $this->meta_value  = $value;

        $this->db->insert('meta_values', $this);
    }
}
