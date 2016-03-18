<?php namespace League\OAuth2\Client\Test\Provider;

use Mockery as m;

class RunkeeperTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new \League\OAuth2\Client\Provider\Runkeeper(
            [
                'clientId'     => 'mock_client_id',
                'clientSecret' => 'mock_secret',
                'redirectUri'  => 'none',
            ]
        );
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testScopes()
    {
        $options = ['scope' => [uniqid(), uniqid()]];
        $url     = $this->provider->getAuthorizationUrl($options);
        $this->assertContains(urlencode(implode(',', $options['scope'])), $url);
    }

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        $this->assertEquals('/apps/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];
        $url    = $this->provider->getBaseAccessTokenUrl($params);
        $uri    = parse_url($url);
        $this->assertEquals('/apps/token', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(
            '{"access_token":"mock_access_token", "scope":"repo,gist", "token_type":"bearer"}'
        );
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testStravaDomainUrls()
    {
        $this->provider->domain = 'https://runkeeper.company.com';
        $response               = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn(
            'access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token&otherKey={1234}'
        );
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertEquals(
            $this->provider->domain . '/apps/authorize',
            $this->provider->getBaseAuthorizationUrl()
        );
        $this->assertEquals(
            $this->provider->domain . '/apps/token',
            $this->provider->getBaseAccessTokenUrl([])
        );
        $this->assertEquals(
            $this->provider->apiDomain . '/user?access_token=' . $token,
            $this->provider->getResourceOwnerDetailsUrl($token)
        );

        $this->assertEquals(
            $this->provider->apiDomain . '/profile?access_token=' . $token,
            $this->provider->getResourceOwnerProfileUrl($token)
        );

    }

    public function testUserData()
    {
        $id          = null;
        $name        = uniqid();
        $email       = uniqid();
        $elite       = false;
        $location    = uniqid();
        $athleteType = uniqid();
        $gender      = uniqid();
        $birthday    = uniqid();
        $profile     = uniqid();

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn(
            'access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token&otherKey={1234}'
        );
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);
        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn(
            '{"userID": "' . $id . '", "name": "' . $name . '", "email": "' . $email . '", "elite": "' . $elite . '", "location": "' . $location . '", "athlete_type": "' . $athleteType . '", "gender": "' . $gender . '", "birthday": "' . $birthday . '", "profile": "' . $profile . '"}'
        );
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(200);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(3)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user  = $this->provider->getResourceOwner($token);
        $this->assertEquals($id, $user->getId());
        $this->assertEquals($id, $user->toArray()['userID']);
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($name, $user->toArray()['name']);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->toArray()['email']);
        $this->assertEquals($elite, $user->getElite());
        $this->assertEquals($elite, $user->toArray()['elite']);
        $this->assertEquals($location, $user->getLocation());
        $this->assertEquals($location, $user->toArray()['location']);
        $this->assertEquals($athleteType, $user->getAthleteType());
        $this->assertEquals($athleteType, $user->toArray()['athlete_type']);
        $this->assertEquals($gender, $user->getGender());
        $this->assertEquals($gender, $user->toArray()['gender']);
        $this->assertEquals($birthday, $user->getBirthday());
        $this->assertEquals($birthday, $user->toArray()['birthday']);
        $this->assertEquals($profile, $user->getProfile());
        $this->assertEquals($profile, $user->toArray()['profile']);
    }

    /**
     * @expectedException \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     **/
    public function testExceptionThrownWhenErrorObjectReceived()
    {
        $message = uniqid();
        $status  = rand(400, 600);

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn(' {"error":"' . $message . '"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);

        $this->provider->setHttpClient($client);
        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }
}