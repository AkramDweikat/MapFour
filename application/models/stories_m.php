<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stories_M extends CI_Model {

    var $guid   = '';
    var $link   = '';
    var $title   = '';
    var $description   = '';
    var $body   = '';
    var $largeimage   = '';
    var $smallimage   = '';
    var $video   = '';
    var $source   = '';
    var $pubDate   = '';
    var $author   = '';

    public function __construct() {
        parent::__construct();
    }
    
    public function findByGuid($guid) {
        $query = $this->db->get_where('stories', array('guid' => $guid));
        return $query->result();
    }

    public function insert($data) {
        $this->guid   = $data->guid;
        $this->link   = $data->link;
        $this->title  = $data->title->{'#cdata-section'};
        $this->description  = $data->description->{'#cdata-section'};
        $this->body  = $data->body->{'#cdata-section'};
        $this->largeimage  = $data->largeimage;
        $this->smallimage  = $data->smallimage;
        $this->video  = $data->video;
        $this->source  = $data->source->{'#cdata-section'};
        $this->pubDate  = $data->pubDate;
        $this->author  = $data->author;

        $this->db->insert('stories', $this);

        $story_id = $this->db->insert_id();
        $this->load->model('Meta_values_M');
        if(isset($data->metadata)){
                foreach ($data->metadata as $type => $values) {
                    if(is_array($values)) {
                        foreach ($values as $value) {
                            $this->Meta_values_M->insert($story_id, $type, $value->{'@value'});
                        }
                    } else {
                        $this->Meta_values_M->insert($story_id, $type, $values->{'@value'});
                    }
                }
            }
        }

    public function update($data) {
        $this->db->where('guid', $data->guid);
        
        $this->guid   = $data->guid;
        $this->link   = $data->link;
        $this->title  = $data->title->{'#cdata-section'};
        $this->description  = $data->description->{'#cdata-section'};
        $this->body  = $data->body->{'#cdata-section'};
        $this->largeimage  = $data->largeimage;
        $this->smallimage  = $data->smallimage;
        $this->video  = $data->video;
        $this->source  = $data->source->{'#cdata-section'};
        $this->pubDate  = $data->pubDate;
        $this->author  = $data->author;

        $this->db->update('stories', $this); 
    }
}