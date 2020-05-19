<?php

namespace nixon\instagram\controllers;

use nixon\instagram\Plugin;

use Craft;
use craft\web\Controller;

/**
 * @author Nixon Design Ltd
 * @since 1.0
 */
class FeedsController extends Controller
{
    protected $allowAnonymous = ['get-media'];

    public function actionGetMedia()
    {
        $request = Craft::$app->getRequest();

        return $this->asJson(Plugin::getInstance()->getFeeds()->getMediaFeed([
            'after' => $request->getParam('after'),
            'before' => $request->getParam('before'),
            'limit' => $request->getParam('limit'),
        ]));
    }
}
