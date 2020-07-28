<?php

namespace nixondesign\instagram\controllers;

use nixondesign\instagram\Plugin;
use nixondesign\instagram\models\Token as Model;
use nixondesign\instagram\records\Token as Record;

use Craft;
use DateInterval;
use DateTime;
use Exception;
use craft\web\Controller;

/**
 * @author Nixon Design Ltd
 * @since 1.0
 */
class AuthController extends Controller
{
    /**
     * Displays the main settings page.
     *
     * @param null $user
     * @param null $siteHandle
     * @return \yii\web\Response
     * @throws \craft\errors\SiteNotFoundException
     */
    public function actionIndex($user = null, $siteHandle = null)
    {
        if ($siteHandle === null) {
            $siteId = Craft::$app->getSites()->getCurrentSite()->id;
        } else {
            $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);
            $siteId = $site->id;
        }

        if ($user === null) {
            $user = Plugin::getInstance()->getAuth()->getAuthBySiteId($siteId);
        }

        return $this->renderTemplate('instagram', [
            'user' => $user,
            'siteId' => $siteId,
            'redirectUrl' => Plugin::getOAuthRedirectUrl(),
        ]);
    }

    public function actionAuthenticate()
    {
        $this->requirePermission('instagram-auth');

        $request = Craft::$app->getRequest();
        $session = Craft::$app->getSession();
        $urlManager = Craft::$app->getUrlManager();

        $session->remove('instagram');

        $siteId = $request->getBodyParam('siteId');
        $clientId = $request->getBodyParam('clientId');
        $clientSecret = $request->getBodyParam('clientSecret');

        $user = new Model();
        $user->clientId = $clientId;
        $user->clientSecret = $clientSecret;

        // Store these for retrieval after redirect to Instagram
        $session->set('instagram', [
            'siteId' => $siteId,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'state' => $user->getState(),
        ]);

        if (!$user->validate()) {
            $session->setError(Craft::t('instagram', 'Unable to authenticate user'));
            $urlManager->setRouteParams([ 'user' => $user ]);

            return null;
        }

        // Redirect to Instagram to get user permissions.
        return $this->redirect($user->getAuthUrl());
    }

    public function actionGetToken()
    {
        $request = Craft::$app->getRequest();
        $session = Craft::$app->getSession();

        $state = $request->getQueryParam('state');
        $sessionState = $session->get('instagram')['state'];
        $code = Craft::$app->request->getQueryParam('code');

        if (!$state || !$sessionState || $state !== $sessionState) {
            $session->remove('instagram');
            $session->setError(Craft::t('instagram', 'Invalid OAuth state'));

            return null;
        }

        $token = Plugin::getInstance()->getAuth()->getToken([
            'code' =>$code,
            'clientId' => $session->get('instagram')['clientId'],
            'clientSecret' => $session->get('instagram')['clientSecret'],
        ]);

        if ($token === null) {
            $session->remove('instagram');
            $session->setError(Craft::t('instagram', 'Authentication unsuccessful'));

            return null;
        }

        $user = new Record([
            'token' => $token['access_token'],
            'siteId' => $session->get('instagram')['siteId'],
            'clientId' => $session->get('instagram')['clientId'],
            'clientSecret' => $session->get('instagram')['clientSecret'],
            'expiryDate' => $this->calculateExpiryDate($token['expires_in']),
        ]);

        $user->save();

        $session->remove('instagram');
        $session->setNotice(Craft::t('instagram', 'Authentication successful'));

        return $this->redirect('instagram');
    }

    /**
     * Delete a token.
     *
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDelete()
    {
        $this->requirePostRequest();
        $this->requirePermission('instagram-delete');

        $session = Craft::$app->getSession();

        $tokenId = Craft::$app->getRequest()->getRequiredBodyParam('tokenId');

        Plugin::getInstance()->getAuth()->deleteTokenById($tokenId);

        $session->setNotice(Craft::t('instagram', 'Token deleted'));

        return $this->redirectToPostedUrl();
    }

    /**
     * Refresh a token.
     *
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionRefresh()
    {
        $this->requirePostRequest();
        $this->requirePermission('instagram-refresh');

        $session = Craft::$app->getSession();
        $tokenId = Craft::$app->getRequest()->getRequiredBodyParam('tokenId');

        Plugin::getInstance()->getAuth()->refreshTokenById($tokenId);

        $session->setNotice(Craft::t('instagram', 'Refreshed token requested'));

        return $this->redirectToPostedUrl();
    }

    /**
     * Convert an expiry duration into a DateTime object.
     *
     * @param int $expiresIn Time to expiry in seconds.
     * @return DateTime
     * @throws Exception
     */
    public function calculateExpiryDate($expiresIn)
    {
        $now = new DateTime();
        $now->add(new DateInterval('PT' . $expiresIn . 'S'));

        return $now;
    }
}
