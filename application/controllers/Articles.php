<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Articles extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        //load the Articles Model on init
        $this->load->model('Articles_M');
    }

    public function index($start, $end, $location)
    {
        //get articles using module with params
        $data['articles'] = $this->Articles_M->get_articles($start, $end, $location);

        //return json response to view
        $this->load->view('frontpage', $data);
    }

}
?>