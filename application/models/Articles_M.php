<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Articles_M extends CI_Model {

    public function get_articles($start, $end, $location){

        $articles = array();

        return json_encode($articles);

    }

}
