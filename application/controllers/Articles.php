<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Articles extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        //load the Articles Model on init
        $this->load->model('Articles_m');
        $this->load->model('Stories_M');
    }

    public function index()
    {

        $start = $_GET['start'];
        $end = $_GET['end'];

        $tl = $_GET['br'];
        $br = $_GET['tl'];

        $q = null;

        if(isset($_GET['q'])){
            $q = $_GET['q'];
        }

        //get articles using module with params
        $data['articles'] = $this->Articles_m->get_articles($start, $end, $tl, $br, $q);

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    public function related() {
        $story_id = $_GET['story_id'];
        $data['articles'] = $this->Stories_M->findRelatedStories($story_id);

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}
?>