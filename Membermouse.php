<?php


namespace app\Helpers;

use App\Helpers\Webhooks;
use Exception;
use GuzzleHttp\Client;


/**
 * Full documentation of this API:
 * http://support.membermouse.com/support/solutions/articles/9000020268-api-documentation
 *
 * Class Membermouse
 * @package app\Helpers
 */
class Membermouse implements MembershipPlatformInterface
{

    protected $api_key;

    protected $api_url;

    protected $api_secret;

    public function __construct($api_key, $api_url, $api_secret)
    {
        $this->api_key     = $api_key;
        $this->$api_url    = $api_url;
        $this->$api_secret = $api_secret;
    }


    /**
     * http://{Your_API_URL}?q=/createMember
     *
     * @param array $data = ['apikey', 'apisecret', 'membership_level_id', 'email'] - required
     * @param $plan_id
     * @return mixed
     * @throws Exception
     */
    public function createData(Array $data, $plan_id)
    {
        $data = [
            'apikey'              => $this->api_key,
            'apisecret'           => $this->api_secret,
            'membership_level_id' => $data['membership_level_id'],
            'email'               => $data['email']
        ];

        $callback_data = [
            'platform_name' => 'Membermouse',
            'external_user_id' => $data['external_user_id']
        ];

        $url = $this->api_url."?q=/createMember";

        return Webhooks::insert($url, $data, 2, $plan_id, array(), MembershipIntegration::callback, $callback_data);
    }

    /**
     * http://{Your_API_URL}?q=/getMember
     *
     * member_id  Yes, if email address is not provided
     * email      Yes, if member ID is not provided
     *
     * @param array $data = ['apikey', 'apisecret', 'member_id' OR 'email'] - required
     * @param null $user_id
     * @return mixed
     * @throws Exception
     */
    public function retrieveData(Array $data = null, $user_id = null, $plan_id)
    {
        if(isset($user_id)) {
            $data = [
                'apikey'    => $this->api_key,
                'apisecret' => $this->api_secret,
                'member_id' => $user_id
            ];
        } else {
            $data = [
                'apikey'    => $this->api_key,
                'apisecret' => $this->api_secret,
                'email'     => $data['email']
            ];
        }

        $url = $this->api_url."?q=/getMember";

        return Webhooks::insert($url, $data, 2, $plan_id);
    }

    /**
     * http://{Your_API_URL}?q=/updateMember
     *
     * member_id  Yes, if email address is not provided
     * email      Yes, if member ID is not provided
     *
     * @param array $data = ['apikey', 'apisecret', 'member_id' OR 'email'] - required
     * @param $user_id
     * @param $plan_id
     * @return mixed
     * @throws Exception
     */
    public function updateData(Array $data, $user_id = null, $plan_id)
    {
        if(isset($user_id)) {
            $data = [
                'apikey'    => $this->api_key,
                'apisecret' => $this->api_secret,
                'member_id' => $user_id
            ];
        } else {
            $data = [
                'apikey'    => $this->api_key,
                'apisecret' => $this->api_secret,
                'email'     => $data['email']
            ];
        }

        $url = $this->api_url."?q=/updateMember";

        return Webhooks::insert($url, $data, 2, $plan_id);
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
        if(isset($user_id)) {
            $data = [
                'apikey'    => $this->api_key,
                'apisecret' => $this->api_secret,
                'member_id' => $user_id
            ];
        } else {
            $data = [
                'apikey'    => $this->api_key,
                'apisecret' => $this->api_secret,
                'email'     => $data['email']
            ];
        }

        //Set status 2 (Cancelled)
        $data['status']  = 2;

        $url = $this->api_url."?q=/updateMember";

        return Webhooks::insert($url, $data, 2, $plan_id);
    }


    /**
     * Get Membership levels for current platform
     *
     * @param null $plan_id
     * @return mixed
     */
    public function getLevels($plan_id = null)
    {
        $default_levels = ['Basic level', 'Pro level', 'Premium level'];

        $levels = [];

        foreach($default_levels as $key => $value) {
            $levels[] = array(
                'id' => $key+1,
                'name' => $value
            );
        }

        return $levels;
    }
}