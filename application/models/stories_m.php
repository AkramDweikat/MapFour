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

    public function findRelatedStories($story_id) {
        $query = $this->db->query("SELECT subq.*, SUM(subq.weight) as ranking
            FROM (
                SELECT s_rel.*,
                  case mv.meta_type
                     when 'Person' then 5
                     when 'Facility' then 5
                     when 'PoliticalEvent' then 5
                     when 'Product' then 5
                     when 'Organization' then 4
                     when 'IndustryTerm' then 4
                     when 'ProgrammingLanguage' then 4
                     when 'EntertainmentAwardEvent' then 4
                     when 'NaturalFeature' then 4
                     when 'PublishedMedium' then 4
                     when 'City' then 4
                     when 'SportsEvent' then 4
                     when 'SportsLeague' then 4
                     when 'MedicalCondition' then 4
                     when 'Company' then 3
                     when 'RadioStation' then 3
                     when 'TVStation' then 3
                     when 'TVShow' then 3
                     when 'Movie' then 3
                     when 'Technology' then 3
                     when 'Holiday' then 3
                     when 'OperatingSystem' then 3
                     when 'MedicalTreatment' then 3
                     when 'SportsGame' then 3
                     when 'MarketIndex' then 3
                     when 'MusicAlbum' then 3
                     when 'Country' then 2
                     when 'Currency' then 1
                     when 'ProvinceOrState' then 1
                     when 'Continent' then 1
                     when 'Region' then 2
                     when 'Position' then 2
                     else 1
                  end as weight
                FROM stories s
                  INNER JOIN meta_values mv ON (s.id = mv.story_id),
                     stories s_rel
                  INNER JOIN meta_values mv_rel ON (s_rel.id = mv_rel.story_id)
                WHERE s.id = $story_id
                AND mv_rel.meta_type = mv.meta_type
                AND mv_rel.meta_value = mv.meta_value
            ) as subq
            GROUP BY subq.id
            ORDER BY SUM(subq.weight) DESC", FALSE);
        $result = array();
        foreach ($query->result() as $data) {
            // $result[] = new self($data);
            $result[] = $data;
        }
        return $result;
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
        $location_metas = array('Town', 'City', 'County', 'ProvinceOrState', 'Country');

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
