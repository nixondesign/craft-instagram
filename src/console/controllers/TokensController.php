<?php

namespace nixon\instagram\console\controllers;

use nixon\instagram\Plugin;
use nixon\instagram\records\Token;

use craft\console\Controller;

/**
 * @author Nixon Design Ltd
 * @since 1.0
 */
class TokensController extends Controller
{
    /**
     * Refreshes all access tokens.
     */
    public function actionRefresh()
    {
        foreach (Token::find()->all() as $user) {
            Plugin::getInstance()->getAuth()->refreshToken($user);
        }
    }
}
