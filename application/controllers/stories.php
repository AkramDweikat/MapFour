<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stories extends CI_Controller {
	public function index() {
		$response = \Httpful\Request::get('http://devapi.aljazeera.com/v1/en/stories/latest?format=json&apikey=UBsPSKqownYvsDaE9l96Jq1aAOQOHXgF')->send();

		foreach ($response->body->stories as $story) {
			error_log("STORY: " . json_encode($story));

			// Fetch existing record with same guid
			$this->load->model('Stories_M');
      $record = $this->Stories_M->findByGuid($story->guid);
			if ($record) {
				error_log("FOUND RECORD: " . json_encode($record));
			} else {
				error_log("INSERT RECORD");
				$this->Stories_M->insert($story);
			}
		}

		$this->load->view('stories_parser');
	}
}
