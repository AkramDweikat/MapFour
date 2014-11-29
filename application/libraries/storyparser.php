<?php

class Storyparser {
	public function parseStores() {
		$response = \Httpful\Request::get('http://devapi.aljazeera.com/v1/en/stories/latest?format=json&apikey=UBsPSKqownYvsDaE9l96Jq1aAOQOHXgF')->send();
		error_log("RESPONSE: " . json_encode($response));
	}
}