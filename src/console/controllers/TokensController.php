<?php

namespace nixondesign\instagram\console\controllers;

use nixondesign\instagram\Plugin;
use nixondesign\instagram\records\Token;
use yii\console\ExitCode;
use craft\console\Controller;

/**
 * Manages Instagram tokens.
 *
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

        return ExitCode::OK;
    }
}
