<?php

namespace App\Helpers;


interface MembershipPlatformInterface
{
    /**
     * @param $data
     * @return mixed
     */
    public function createData(Array $data, $plan_id);

    /**
     * @param array $data
     * @param null $user_id
     * @param $plan_id
     * @return mixed
     */
    public function retrieveData(Array $data = null, $user_id = null, $plan_id);

    /**
     * @param array $data
     * @param $user_id
     * @param $plan_id
     * @return mixed
     */
    public function updateData(Array $data, $user_id = null, $plan_id);

    /**
     * @param array $data
     * @param $user_id
     * @param $plan_id
     * @return mixed
     */
    public function deleteData(Array $data, $user_id = null, $plan_id);

    /**
     * Get Membership levels for current platform
     *
     * @param null $plan_id
     * @return mixed
     */
    public function getLevels($plan_id = null);
}
