<?php

namespace nixon\instagram\services;

use nixon\instagram\Plugin;
use nixon\instagram\models\Token as Model;
use nixon\instagram\records\Token as Record;

use Craft;
use DateInterval;
use DateTime;
use craft\base\Component;
use craft\helpers\Json;

/**
 * @author Nixon Design Ltd
 * @since 1.0
 */
class Auth extends Component
{
    /**
     * Gets a short lived token and then immediately exchanges it for a long
     * lived token.
     */
    public function getToken($params)
    {
        $shortLivedToken = $this->getShortLivedToken(
            $params['clientId'],
            $params['clientSecret'],
            $params['code']
        );

        return $this->getLongLivedToken($shortLivedToken, $params['clientSecret']);
    }

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $code
     * @return mixed
     */
    public function getShortLivedToken(string $clientId, string $clientSecret, string $code)
    {
        $client = $this->getClient();

        $response = $client->request('POST', '/oauth/access_token', [
            'form_params' => [
                'code' => $code,
                'grant_type' => 'authorization_code',
                'client_id' => Craft::parseEnv($clientId),
                'client_secret' => Craft::parseEnv($clientSecret),
                'redirect_uri' => Plugin::getOAuthRedirectUrl(),
            ]
        ]);

        return Json::decode($response->getBody())['access_token'];
    }

    /**
     * @param string $token
     * @param string $clientSecret
     * @return mixed
     */
    public function getLongLivedToken(string $token, string $clientSecret)
    {
        $client = $this->getClient();

        $query = [
            'grant_type' => 'ig_exchange_token',
            'access_token' => Craft::parseEnv($token),
            'client_secret' => Craft::parseEnv($clientSecret),
        ];

        $response = $client->request('GET', '/access_token', [
            'query' => $query,
            'base_uri' => Plugin::GRAPH_ENDPOINT,
        ]);

        return Json::decode($response->getBody());
    }

    public function getAuthBySiteId(int $siteId = null)
    {
        $record = Record::findOne([
            'siteId' => $siteId
        ]);

        if ($record === null) {
            return null;
        }

        $model = new Model();
        $model->setAttributes($record->toArray());

        return $record;
    }

    /**
     * @param int $tokenId
     * @return Record|null
     */
    public function getTokenById(int $tokenId)
    {
        $record = Record::findOne([
            'id' => $tokenId,
        ]);

        if ($record === null) {
            return null;
        }

        return $record;
    }

    /**
     * @param int $siteId
     * @return Record|null
     */
    public function getTokenBySiteId(int $siteId)
    {
        $record = Record::findOne([
            'siteId' => $siteId
        ]);

        if ($record === null) {
            return null;
        }

        return $record;
    }

    public function refreshTokenById(int $tokenId)
    {
        $token = $this->getTokenById($tokenId);

        if ($token === null) {
            return;
        }

        $this->refreshToken($token);
    }

    public function refreshToken(Record $token)
    {
        $client = Craft::createGuzzleClient([
            'base_uri' => Plugin::GRAPH_ENDPOINT,
        ]);

        $request = $client->get('refresh_access_token', [
            'query' => [
                'access_token' => $token->token,
                'grant_type' => 'ig_refresh_token',
            ],
        ]);

        $json = Json::decode($request->getBody());

        $token->token = $json['access_token'];
        $token->expiryDate = $this->calculateExpiryDate($json['expires_in']);

        $token->update();
    }

    public function deleteTokenById(int $tokenId)
    {
        $record = Record::findOne([
            'id' => $tokenId,
        ]);

        if ($record === null) {
            return null;
        }

        return $record->delete();
    }

    public function calculateExpiryDate($expiresIn)
    {
        $now = new DateTime();
        $now->add(new DateInterval('PT' . $expiresIn . 'S'));

        return $now;
    }

    public function getClient()
    {
        return Craft::createGuzzleClient([
            'base_uri' => Plugin::API_ENDPOINT,
            'http_errors' => false,
        ]);
    }
}
