<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stories extends CI_Controller {
	public function index() {
		$pagenumber = 1;
		for ($pagenumber = 0; $pagenumber <= 10; $pagenumber++) {
			$response = \Httpful\Request::get('http://devapi.aljazeera.com/v1/en/stories/latest?format=json&apikey=UBsPSKqownYvsDaE9l96Jq1aAOQOHXgF&pagenumber=' . $pagenumber)->send();

			foreach ($response->body->stories as $story) {

				$this->load->model('Stories_M');
	      $record = $this->Stories_M->findByGuid($story->guid);
				if ($record) {
					$this->Stories_M->update($story);
				} else {
					$this->Stories_M->insert($story);
				}
			}
		}

		$this->load->view('stories_parser');
	}
}
