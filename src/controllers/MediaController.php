<?php

namespace nixondesign\instagram\controllers;

use nixondesign\instagram\Plugin;

use Craft;
use craft\web\Controller;

/**
 * @author Nixon Design Ltd
 * @since 1.0
 */
class MediaController extends Controller
{
    protected array|int|bool $allowAnonymous = ['fetch'];

    public function actionFetch()
    {
        $request = Craft::$app->getRequest();

        return $this->asJson(Plugin::getInstance()->getMedia()->getMedia([
            'after' => $request->getParam('after'),
            'before' => $request->getParam('before'),
            'limit' => $request->getParam('limit'),
        ]));
    }
}
