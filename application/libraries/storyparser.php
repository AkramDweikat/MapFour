<?php

class Storyparser {
	public function parseStores() {
		$response = \Httpful\Request::get('http://devapi.aljazeera.com/v1/en/stories/latest?format=json&apikey=UBsPSKqownYvsDaE9l96Jq1aAOQOHXgF')->send();
		
		foreach ($response->body->stories as $story) {
			error_log("STORY: " . json_encode($story));
		}
	}
}