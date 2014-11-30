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
    var $latitude = '';
    var $longitude = '';

    public function __construct() {
        parent::__construct();
    }
    
    public function findByGuid($guid) {
        $query = $this->db->get_where('stories', array('guid' => $guid));
        return $query->num_rows() > 0 ? new self(array_shift($query->result())) : null;
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
        $this->pubDate = date('Y-m-d H:i:s', strtotime($data->pubDate));

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

        $this->geocode($story_id);
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
        $this->pubDate = date('Y-m-d H:i:s', strtotime($data->pubDate));
        $this->author  = $data->author;

        $this->db->update('stories', $this);
    }

    public function geocode($story_id) {
        if(!isset($this->latitude) || $this->latitude == 0.0 || $this->latitude == "") {
            $address = $this->mostGranularLocation($story_id);

            $api_key = $this->config->item('google_geocode_api_key');
            // URL TO HTTP REQUEST
            $link = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=".$api_key."&sensor=false&oe=utf8";

            // WE GET FILE CONTENT
            $page = json_decode(file_get_contents($link), TRUE);

            $status = $page['status'];
            if($status=="OK") {
                $result = $page['results'][0];
                $location = $result['geometry']['location'];
                $geoloc = array();
                $geoloc['latitude'] = floatval($location['lat']);
                $geoloc['longitude'] = floatval($location['lng']);

                $this->db->where('id', $story_id);
                $this->db->update('stories', $geoloc);
            }
        }
    }

    private function mostGranularLocation($story_id) {
        $location_metas = array('Town', 'City', 'County', 'Province', 'State', 'Country');

        $this->db->select("*");
        $this->db->from('meta_values');
        $this->db->where('story_id', $story_id);
        $result = $this->db->get();
        $all_metas = $result->result_array();

        foreach($location_metas as $lm){
            foreach ($all_metas as $meta) {
                if($meta['meta_type'] == $lm){
                    return $meta['meta_value'];
                }
            }
        }
    }
}
