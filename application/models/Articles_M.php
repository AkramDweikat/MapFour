<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Articles_M extends CI_Model {

    public function get_articles($start, $end, $location){

        //db query to retrieve articles
        $this->db->select("*");
        $this->db->from('stories');
        $result = $this->db->get();
        $all_stories = $result->result_array();

        //filter articles by time bounds
        $articles = $this->filter_by_time($all_stories, $start, $end);

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


}
