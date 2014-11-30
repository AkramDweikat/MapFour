<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Articles_m extends CI_Model {

    public function get_articles($start, $end, $tl, $br, $q=null){
            //process location bounds
            $tl = explode(',', $tl);
            $br = explode(',', $br);

            $minx = floatval($tl[0]);
            $maxx = floatval($br[0]);
            $miny = floatval($br[1]);
            $maxy = floatval($tl[1]);

            //create query string
            $this->db->select('stories.id, stories.guid, stories.link, stories.title, stories.description, stories.body, stories.pubDate, stories.latitude,stories.longitude, stories.largeimage, stories.smallimage, stories.source, stories.author, GROUP_CONCAT(meta_values.meta_value SEPARATOR ",") as mv', false);

            $this->db->from('stories');
            if($q!=null){
                $this->db->join("meta_values", "stories.id=meta_values.story_id and meta_values.meta_value like '".$q."'");
            }else{
                $this->db->join("meta_values", "stories.id=meta_values.story_id");
            }
            //faster time filtering
            $this->db->where("UNIX_TIMESTAMP(`pubDate`) BETWEEN '".$start."' AND '".$end."'");
            $this->db->where("latitude BETWEEN '".$minx."' AND '".$maxx."'");
            $this->db->where("longitude BETWEEN '".$miny."' AND '".$maxy."'");
            //check for search term query

            $this->db->group_by('stories.id');




            $result = $this->db->get();

            $articles = $result->result_array();

        return $articles;

    }

    public function get_by_term($q, $articles){

        return $articles;

    }


    public function filter_by_time($all_stories, $start, $end){

        $articles = array();

        foreach($all_stories as $story){

            $pubDate = strtotime($story['pubDate']);
            //$pubDate = $story['pubDate'];
            if(($pubDate>=$start)&&($pubDate<=$end)){
                $articles[] = $story;
            }
        }

        return $articles;
    }


    public function filter_by_location($time_sorted_articles, $location){
        $this->load->library("PointLocation");

        $articles = array();

        foreach($time_sorted_articles as $story){

        //find most granular location
            $story_loc = $this->get_location($story['id']);

            //check if location of article is within bounds
             if($this->pointlocation->pointInPolygon($story_loc, $location)){

                 $articles[] = $story;

             }

        }
        return $articles;
    }

    public function get_all_meta($story_id){
        $this->db->select("*");
        $this->db->from('meta_values');
        $this->db->where('story_id', $story_id);
        $result = $this->db->get();
        $all_metas = $result->result_array();
        return $all_metas;
    }

    public function get_location($story_id){
        $all_metas = $this->get_all_meta($story_id);
        $location_metas = array('Country', 'State', 'Province', 'County', 'City', 'Town');

        $most_granular = null;

        //find most granular
        foreach($location_metas as $lm){

            foreach($all_metas as $meta){

                if($meta['meta_type']==$lm){
                    $most_granular = $meta['meta_value'];
                }
            }

        }

        return $this->geocode($most_granular);
    }

    public function geocode($address){

        $api_key = $this->config->item('google_geocode_api_key');

        // URL TO HTTP REQUEST
        $link = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=".$api_key."&sensor=false&oe=utf8";

        // WE GET FILE CONTENT
        $page = json_decode(file_get_contents($link), TRUE);


        $status = $page['status'];

        if($status!="OK"){

            $location = "0, 0";

        }else{

            $result = $page['results'][0];
            $location = $result['geometry']['location'];
            $location = $location['lat'].", ". $location['lng'];

        }

        return $location;

    }
}
