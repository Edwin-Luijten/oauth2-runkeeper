<?php

namespace League\OAuth2\Client\Provider;

class RunkeeperResourceOwner implements ResourceOwnerInterface
{
    /**
     * Domain
     *
     * @var string
     */
    protected $domain;

    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array $response
     */
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     * Runkeeper has no id when profile requested
     *
     * @return null
     */
    public function getId()
    {
        return $this->response['userID'] ?: null;
    }

    /**
     * Get resource owner email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['email'] ?: null;
    }

    /**
     * Get resource owner name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->response['name'] ?: null;
    }

    /**
     * Get resource owner location
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->response['location'] ?: null;
    }

    /**
     * Get resource owner athlete type
     *
     * @return string|null
     */
    public function getAthleteType()
    {
        return $this->response['athlete_type'] ?: null;
    }

    /**
     * Get resource owner gender
     *
     * @return string|null
     */
    public function getGender()
    {
        return $this->response['gender'] ?: null;
    }

    /**
     * Get resource owner birthdat
     *
     * @return string|null
     */
    public function getBirthday()
    {
        return $this->response['birthday'] ?: null;
    }

    /**
     * Get resource owner elite status (subscriber)
     *
     * @return boolean|false
     */
    public function getElite()
    {
        return $this->response['elite'] ?: false;
    }

    /**
     * Get resource owner public profile url
     *
     * @return boolean|false
     */
    public function getProfile()
    {
        return $this->response['profile'] ?: false;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
