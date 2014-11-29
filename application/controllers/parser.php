<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Parser extends CI_Controller {
	public function index() {
		$parser = new storyParser();
		$parser->parseStores();

		$this->load->view('welcome_message');
	}
}
