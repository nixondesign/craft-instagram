<?php

namespace nixondesign\instagram;

use Craft;

/**
 * @author Nixon Design Ltd
 * @since 1.0
 */
class Variable
{
    /**
     * @param null $id
     * @param array $options
     * @return array|null
     */
    public function getMedia(array $options = [])
    {
        return Plugin::getInstance()->getMedia()->getMedia($options);
    }

    public function isAuthorised(): bool
    {
        $siteId = Craft::$app->getSites()->getCurrentSite()->id;

        $token = Plugin::getInstance()->getAuth()->getTokenBySiteId($siteId);

        return (bool) $token;
    }
}
