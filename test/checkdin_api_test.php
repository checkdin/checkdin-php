<?php
require_once 'inc/checkdin_api.php';

// A standin class useful for testing
class FakeConfig {
  function __construct($apiBaseUrl,
                       $clientID = '99',
                       $clientSecret = '55') {
    $this->apiBaseUrl = $apiBaseUrl;
    $this->clientID = $clientID;
    $this->clientSecret = $clientSecret;
  }

  function clientID() {
    return $this->clientID;
  }

  function clientSecret() {
    return $this->clientSecret;
  }

  function apiBaseUrl() {
    return $this->apiBaseUrl;
  }
}

class FakeRequester {
  function __construct($supported_url, $response) {
    $this->supported_url = $supported_url;
    $this->response = $response;
  }

  function performGet($url) {
    assert_equal($this->supported_url, $url);
    return $this->response;
  }

  function performPost($url) {
    assert_equal($this->supported_url, $url);
    return $this->response;
  }

}

class CheckdinApiTest {
  function test_version() {
    assert_equal(Checkdin\Api::VERSION, '0.0.1');
  }

  function test_default_config_instantiation() {
    $instance = new Checkdin\Api();
    $actual = $instance->apiUrlTemplate();
    $expected = 'https://app.checkd.in/api/v1/{api_action}.json?client_id={client_id}&client_secret={client_secret}&{extra_arguments}';
    assert_equal($actual, $expected);
  }

  function test_specific_config_instantiation() {
    $expected_url = 'http://localhost:9030/api/v1/{api_action}.json?client_id={client_id}&client_secret={client_secret}&{extra_arguments}';
    $config = new FakeConfig('http://localhost:9030');
    $instance = new Checkdin\Api($config);
    assert_equal($instance->apiUrlTemplate(), $expected_url);
  }

  function test_get_users() {
    $config = new FakeConfig('http://localhost:9030');
    $requester = new FakeRequester(
      'http://localhost:9030/api/v1/users.json?client_id=99&client_secret=55&',
      array('thing' => 'more')
    );
    $instance = new Checkdin\Api($config, $requester);

    $response = $instance->getUsers();
    assert_equal($response, array('thing' => 'more'));
  }

  function test_create_user() {
    $config = new FakeConfig('http://localhost:3000');
    $requester = new FakeRequester(
      'http://localhost:3000/api/v1/users.json?client_id=99&client_secret=55&',
      array(
        'user' => array(
          'id' => 123,
          'email' => 'joe@example.com'
        )
      )
    );
    // $requester = new Checkdin\Request();
    $instance = new Checkdin\Api($config, $requester);

    $response = $instance->createUser(array(
      'email' => 'joe@example.com',
      'identifier' => 'you-know-joe'
    ));
    assert_equal($response['user']['email'], 'joe@example.com');
  }

  function test_get_user_authentications() {
    $config = new FakeConfig('http://localhost:9030');
    $requester = new FakeRequester(
      'http://localhost:9030/api/v1/users/27/authentications.json?client_id=99&client_secret=55&',
      array('thing' => 'more')
    );
    $instance = new Checkdin\Api($config, $requester);

    $response = $instance->getUserAuthentications(27);
    assert_equal($response, array('thing' => 'more'));
  }

  function test_expand_url() {
    $config = new FakeConfig('http://localhost:9030');
    $instance = new Checkdin\Api($config);
    $data = array(
      'campaign_id' => 7,
      'page' => 3,
      'per_page' => 500
    );
    $actual = $instance->expandUrl("campaigns/{campaign_id}/leaderboard", $data);
    $expected = 'http://localhost:9030/api/v1/campaigns/7/leaderboard.json?client_id=99&client_secret=55&page=3&per_page=500';
    assert_equal($actual, $expected);
  }

  function test_expand_url_no_extra() {
    $config = new FakeConfig('http://localhost:9030');
    $instance = new Checkdin\Api($config);
    $actual = $instance->expandUrl("campaigns", array());
    $expected = 'http://localhost:9030/api/v1/campaigns.json?client_id=99&client_secret=55&';
    assert_equal($actual, $expected);
  }

}

array_push($all_test_classes, 'CheckdinApiTest');

?>