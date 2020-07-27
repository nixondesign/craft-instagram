<?php

namespace nixon\instagram\queue\jobs;

use nixon\instagram\Plugin;
use nixon\instagram\records\Token;

use Craft;
use craft\queue\BaseJob;

/**
 * @author Nixon Design Ltd
 * @since 1.0
 */
class RefreshTokens extends BaseJob
{
    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        foreach (Token::find()->all() as $user) {
            Plugin::getInstance()->getAuth()->refreshToken($user);
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('instagram', 'refreshingTokensJobMessage');
    }
}
