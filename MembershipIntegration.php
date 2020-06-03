<?php

namespace App\Helpers;


class MembershipIntegration
{

    /**
     * Generate new object by name
     *
     * @param $platform_name
     * @param $api_key
     * @param null $url
     * @param null $api_secret
     * @return MembershipPlatformInterface
     */
    public static function platformFactory($platform_name, $api_key, $url = null, $api_secret = null)
    {

        switch ($platform_name) {
            case 'Wishlist':
                return new WishList($api_key, $url);
            case 'AMember':
                return new AMember($api_key, $url);
            case 'Kajabi':
                return new Kajabi($url);
            case 'Membermouse':
                return new Membermouse($api_key, $url, $api_secret);
            case 'Memberful':
                return new Memberful($api_key, $url);
            case 'S2member':
                return new S2member($api_key, $url);
            case 'OptimizeMember':
                return new OptimizeMember($api_key, $url);
            case 'Digital Access Pass':
                return new  DigitalAccessPass($api_key, $url);
        }
    }

    /**
     * @param $response
     * @param $callback_data
     * @return void
     */
    public static function callback($response, $callback_data)
    {
        $platform_name = $callback_data['platform_name'];

        switch($platform_name) {
            case 'Kajabi':
                return self::saveNewMemberOfKajabi($callback_data);
            case 'Memberful':
                return self::saveNewMemberOfMemberful($response, $callback_data);
            case 'S2member':
                return self::saveNewMemberOfS2member($response, $callback_data);
            case 'Membermouse':
                return self::saveNewMemberOfMembermouse($response, $callback_data);

        }
    }

    public static function saveNewMemberOfKajabi($callback_data)
    {
        // User ID of buyer
        $external_user_id = $callback_data['external_user_id'];

        // Link current buyer with new added member in membership platform
        DB::table('buyers')->where('id', $external_user_id)->update(['membership_platforms_member_id' => $external_user_id]);
    }

    public static function saveNewMemberOfMemberful($response, $callback_data)
    {
        // User ID of buyer
        $external_user_id = $callback_data['external_user_id'];

        // Response returned by Memberful API
        $response_content = json_decode($response->getBody()->getContents(), true);

        // New added member ID in Memberful Platform
        $member_id = $response_content['member']['id'];

        // Link current buyer with new added member in membership platform
        DB::table('buyers')->where('id', $external_user_id)->update(['membership_platforms_member_id' => $member_id]);

        Memberful::associateOrderWithMember($member_id, $callback_data);
    }

    public static function saveNewMemberOfMembermouse($response, $callback_data)
    {
        // User ID of buyer
        $external_user_id = $callback_data['external_user_id'];

        // Response returned by Memberful API
        $response_content = json_decode($response->getBody()->getContents(), true);

        // New added member ID in Memberful Platform
        $member_id = $response_content['member_id'];

        // Link current buyer with new added member in membership platform
        DB::table('buyers')->where('id', $external_user_id)->update(['membership_platforms_member_id' => $member_id]);
    }

    public static function saveNewMemberOfS2member($response, $callback_data)
    {
        // User ID of buyer
        $external_user_id = $callback_data['external_user_id'];

        if (!empty($response) && !preg_match ("/^Error\:/i", $response) && is_array($user = @unserialize ($response))) {
            $member_id = $user["ID"];
        }

        // Link current buyer with new added member in membership platform
        DB::table('buyers')->where('id', $external_user_id)->update(['membership_platforms_member_id' => $member_id]);
    }
}