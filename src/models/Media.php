<?php

namespace nixondesign\instagram\models;

use craft\base\Model;
use craft\helpers\Html;
use craft\helpers\Template;
use Twig\Markup;

/**
 * @property string $url
 * @property Markup|null $img
 *
 * @author Nixon Design Ltd
 * @since 1.0
 */
class Media extends Model
{
    public $id;

    public $caption;

    public $username;

    public $timestamp;

    public $permalink;

    public $mediaUrl;

    public $mediaType;

    public $thumbnailUrl;

    /**
     * Returns the URL to either the image, carousel or video thumbnail.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->thumbnailUrl ?? $this->mediaUrl ?? null;
    }

    /**
     * Returns an `<img>` tag for this Media object.
     *
     * @param array $options
     * @return Markup|null
     */
    public function getImg(array $options = [])
    {
        if (($url = $this->getUrl()) === null) {
            return null;
        }

        return Template::raw(Html::img($this->getUrl(), $options));
    }
}
