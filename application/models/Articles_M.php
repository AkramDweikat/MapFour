<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Articles_M extends CI_Model {

    public function get_articles($start, $end, $location){

        //db query to retrieve articles
        $this->db->select("*");
        $this->db->from('stories');
        $result = $this->db->get();
        $all_stories = $result->result_array();

        //filter articles by time bounds
        $time_sorted_articles = $this->filter_by_time($all_stories, $start, $end);

        //filter articles by location bounds
        $articles = $this->filter_by_location($time_sorted_articles, $location);

        //return json response
        return json_encode($articles);

    }

    public function filter_by_time($all_stories, $start, $end){

        $articles = array();

        foreach($all_stories as $story){

            $pubDate = strtotime($story['pubDate']);

            if(($pubDate>=$start)&&($pubDate<=$end)){
                $articles[] = $story;
            }
        }

        return $articles;
    }


    public function filter_by_location($time_sorted_articles, $location){

        $articles = array();

        foreach($time_sorted_articles as $story){

        //find most granular location
            $story_loc = $this->get_location($story['guid']);
        //geocode location
        //check if location of article is within bounds

        }
        return $articles;
    }

    public function get_location($story_id){
        $this->db->select("*");
        $this->db->from('meta_values');
        $this->db->where('story_id', $lm);
        $result = $this->db->get();
        $all_metas = $result->result_array();

        $location_metas = array('Country', 'State', 'Province', 'County', 'City', 'Town');

        $most_granular = null;
        //find most granular
        foreach($location_metas as $lm){

            if(array_key_exists($all_metas, $lm)){
                $most_granular = $all_metas[$lm];
            }

        }


        return geocode($most_granular);
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
