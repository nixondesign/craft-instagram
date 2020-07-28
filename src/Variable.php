<?php

namespace nixondesign\instagram;

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
}
