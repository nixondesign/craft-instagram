<?php

namespace nixondesign\instagram\models;

use nixondesign\instagram\Plugin;

use Craft;
use craft\base\Model;
use craft\helpers\UrlHelper;

/**
 * @property string|null $authUrl
 *
 * @author Nixon Design Ltd
 * @since 1.0
 */
class Token extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $clientId;

    /**
     * @var string
     */
    public $clientSecret;

    /**
     * @var string|null
     */
    public $token;

    /**
     * @var
     */
    public $expiryDate;

    public $state;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['clientId', 'required'],
            ['clientSecret', 'required'],
            ['clientId', 'validateClientId'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'clientId' => 'Client ID',
            'clientSecret' => 'Client Secret',
        ];
    }

    /**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();
        $attributes[] = 'expiryDate';

        return $attributes;
    }

    /**
     * Validate the App ID by making a request to the auth url.
     *
     * @param $attribute
     * @param $params
     * @param $validator
     */
    public function validateClientId($attribute, $params, $validator)
    {
        Craft::createGuzzleClient()->get($this->getAuthUrl());
    }

    /**
     * @return string
     */
    public function getAuthUrl()
    {
        return UrlHelper::urlWithParams(Plugin::API_ENDPOINT . '/oauth/authorize', [
            'response_type' => 'code',
            'client_id' => Craft::parseEnv($this->clientId),
            'redirect_uri' => Plugin::getOAuthRedirectUrl(),
            'scope' => implode(',', ['user_profile', 'user_media']),
            'state' => $this->getState(),
        ]);
    }

    public function getState()
    {
        if (!$this->state) {
            $this->state = bin2hex(random_bytes(16));
        }

        return $this->state;
    }
}
