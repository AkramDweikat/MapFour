<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stories_M extends CI_Model {

    var $guid   = '';
    var $link   = '';
    var $title   = '';
    var $description   = '';
    var $body   = '';
    
    public function findByGuid($guid) {
        $query = $this->db->get_where('stories', array('guid' => $guid));
        return $query->result();
    }

    public function insert($data) {
        $this->guid   = $data->guid;
        $this->link   = $data->link;
        $this->title   = $data->title;

        $this->db->insert('stories', $this);
    }
}