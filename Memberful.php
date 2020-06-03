<?php


namespace App\Helpers;

use App\Helpers\Webhooks;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;


class Memberful implements MembershipPlatformInterface
{

    /**
     * The identifier of the application, from the Admin panel.
     *
     * @var string
     */
    protected $client_id = 'some_id';

    /**
     * The application's shared secret, also from the Admin panel.
     *
     * @var string
     */
    protected $client_secret = 'client_secret';

    /**
     * This must always be the string authorization_code
     *
     * @var string
     */
    protected $grant_type = 'authorization_code';

    /**
     *  This is the value that was passed to your site via the OAuth
     *
     * @var
     */
    protected $code;

    /**
     * This will prompt the user to sign in, and then send them back to the redirect URL
     * you specified when setting up the custom application.
     *
     * @var string
     */
    protected $auth_url = 'https://{subdomain}.memberful.com/oauth?client_id={application identifier}&response_type=code';

    /**
     * The redirect url will include an extra parameter, code.
     * This code's sole purpose is to be exchanged for an access token.
     * The redirect url will include an extra parameter, code. This code's sole purpose is to be exchanged for an access token
     *
     * @var string
     */
    protected $redirect_url = 'some_url';

    /**
     * To exchange the code for the token, POST the following parameters to the token endpoint URL
     *
     * @var string
     */
    protected $token_endpoint = 'https://yoursite.memberful.com/oauth/token';

    protected $client;

    protected $yoursite;

    protected $api_key;


    public function __construct($api_key, $your_site)
    {
        $this->client   = new Client();
        $this->api_key  = $api_key;
        $this->yoursite = $your_site;
    }

    public function getAuthorizationCode()
    {
//        $response = $this->client->post($this->auth_url, ['body' => array()]);
    }

    /**
     * Get info about member
     *
     * @param $member_id
     * @return array
     */
    public function getMemberInfo($member_id)
    {
        $response = $this->client->get("https://$this->yoursite.memberful.com/admin/members/$member_id.json?auth_token=$this->api_key", [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Fetching members
     *
     * @param $access_token
     * @return mixed
     */
    public function getMembers($access_token)
    {
        $response = $this->client->get("https://{{$this->yoursite}}.memberful.com/admin/members.json?auth_token=$access_token", [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Fetching subscriptions
     * @return array
     */
    public function getSubscriptions()
    {
        $response = $this->client->get("https://$this->yoursite.memberful.com/admin/subscriptions.json?auth_token=$this->api_key", [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Fetching products
     *
     * @param $access_token
     */
    public function getProducts($access_token)
    {
        $response = $this->client->get("https://{{$this->yoursite}}.memberful.com/admin/products.json?auth_token=$access_token", [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);
    }

    /**
     * Importing new members
     *
     * @param Array $data
     */
    public function importingMember($data)
    {
        $member = array(
            'member' => [
                'email'      => $data['email'],
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'password'   => $data['password'],
                'username'   => $data['username']
            ]
        );

        $response = $this->client->post("https://$this->yoursite.memberful.com/admin/members.json?auth_token=$this->api_key", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json'
            ],
            'body' => json_encode($member)
        ]);

        $response_content = json_decode($response->getBody()->getContents(), true);

        $member_id     = $response_content['member']['id'];
        $level_id      = $data['level_id'];

        $this->associateOrderWithMember($member_id, $level_id);
    }

    /**
     *  Import an order and associate it with our newly created member
     *
     * @param $member_id
     * @param $callback_data
     * @return ResponseInterface
     */
    public static function associateOrderWithMember($member_id, $callback_data)
    {
        $order = array(
            'order' => [
                'member_id'     => $member_id,
                'products'      => array(),
                'subscriptions' => array($callback_data['level_id']),
                'receipt'       => '',
                'created_at'    => time()
            ]
        );

        $client = new \GuzzleHttp\Client();
        $yoursite = $callback_data['url'];
        $api_key  = $callback_data['api_key'];

        $response = $client->post("https://$yoursite.memberful.com/admin/orders.json?auth_token=$api_key", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json'
            ],
            'body' => json_encode($order)
        ]);

        return $response;
    }

    /**
     * Create new member
     *
     * @param array $data
     * @param $plan_id
     * @return mixed
     * @throws Exception
     */
    public function createData(Array $data, $plan_id)
    {
        $url = "https://$this->yoursite.memberful.com/admin/members.json?auth_token=$this->api_key";
        $data = [
            'member' => [
                'email'      => $data['email'],
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'password'   => $data['password'],
                'username'   => $data['username']
            ]
        ];
        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json'
        ];

        $callback_data = [
            'platform_name' => 'Memberful',
            'api_key'  => $this->api_key,
            'url'      => $this->yoursite,
            'level_id' => $data['level_id'],
            'external_user_id' => $data['external_user_id']
        ];

        //I should send in callback_data $this->yoursite and $this->api_key
        return Webhooks::insert($url, $data, 2, $plan_id, $headers, \App\Helpers\MembershipIntegration::callback, $callback_data);
    }

    /**
     * @param array $data
     * @param null $user_id
     * @param $plan_id
     * @return mixed
     */
    public function retrieveData(Array $data = null, $user_id = null, $plan_id)
    {
        // TODO: Implement retrieveData() method.
    }

    /**
     * @param array $data
     * @param $user_id
     * @param $plan_id
     * @return mixed
     */
    public function updateData(Array $data, $user_id = null, $plan_id)
    {
        // TODO: Implement updateData() method.
    }

    /**
     * @param array $data
     * @param $user_id
     * @param $plan_id
     * @return mixed
     * @throws Exception
     */
    public function deleteData(Array $data, $user_id = null, $plan_id)
    {
        $url = "https://$this->yoursite.memberful.com/admin/members/$user_id.json?auth_token=$this->api_key";

        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json'
        ];

        return Webhooks::insert($url, array(), 2, $plan_id, $headers);
    }

    /**
     * Get Membership levels for current platform
     *
     * @param null $plan_id
     * @return mixed
     * @throws \Exception
     */
    public function getLevels($plan_id = null)
    {
        $response = $this->client->get("https://$this->yoursite.memberful.com/admin/subscriptions.json?auth_token=$this->api_key", [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        $response = json_decode($response->getBody()->getContents(), true);

        $levels = [];
        foreach($response as $key => $value) {
            $levels[] = array(
                'id'   => $value['id'],
                'name' => $value['name']
            );
        }

        return $levels;
    }
}