<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Articles_m extends CI_Model {

    public function get_articles($start, $end, $tl, $br, $q=null){

        if($q==null){
            //db query to retrieve articles
            $this->db->select("*");
            $this->db->from('stories');

            //faster time filtering
            $this->db->where("UNIX_TIMESTAMP(`pubDate`) BETWEEN '".$start."' AND '".$end."'");
            //faster location filtering
            $tl = explode(',', $tl);
            $br = explode(',', $br);

            $minx = floatval($tl[0]);
            $maxx = floatval($br[0]);
            $miny = floatval($br[1]);
            $maxy = floatval($tl[1]);
            $this->db->where("latitude BETWEEN '".$minx."' AND '".$maxx."'");
            $this->db->where("longitude BETWEEN '".$miny."' AND '".$maxy."'");


            $result = $this->db->get();

            $all_stories = $result->result_array();

            /*
            $geocoded_stories = array();

            foreach($all_stories as $s){
                $s['location'] = $s['latitude'].", ".$s['longitude'];//$this->get_location($s['id']);
                $geocoded_stories[] = $s;
            }
            */

            //filter articles by time bounds
            // $time_sorted_articles = $this->filter_by_time($geocoded_stories, $start, $end);

            //filter articles by location bounds
            //$articles = $this->filter_by_location($time_sorted_articles, $location);

            $articles = $all_stories;
        }else{

            $nytimes_key = $this->config->item('nytimes_key');

            $start = date("Ymd", $start);
            $end = date("Ymd", $end);

            $url = "http://api.nytimes.com/svc/search/v2/articlesearch.json?callback=svc_search_v2_articlesearch&q=".$q."&sort=newest&hl=true&api-key=".$nytimes_key."&begin_date=".$start."&end_date=".$end;

            $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

            $articles = file_get_contents($url,false,$context);

        }

        //return json response
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
