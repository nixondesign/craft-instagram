<?php

namespace nixon\instagram;

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
    public function getFeed(array $options = [])
    {
        return Plugin::getInstance()->getFeeds()->getMediaFeed($options);
    }
}
