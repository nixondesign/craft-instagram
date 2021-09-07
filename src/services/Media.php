<?php

namespace nixondesign\instagram\services;

use nixondesign\instagram\Plugin;
use nixondesign\instagram\models\Media as MediaModel;

use Craft;
use GuzzleHttp\Exception\RequestException;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use yii\caching\TagDependency;

/**
 * @author Nixon Design Ltd
 * @since 1.0
 */
class Media extends Component
{
    const CACHE_TAG = 'instagram';

    const USER_ID = 'me';

    const MEDIA_FIELDS = [
        'id',
        'caption',
        'username',
        'timestamp',
        'permalink',
        'media_url',
        'media_type',
        'thumbnail_url',
    ];

    public $token;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $siteId = Craft::$app->getSites()->getCurrentSite()->id;

        $token = Plugin::getInstance()->getAuth()->getTokenBySiteId($siteId);

        if ($token) {
            $this->token = $token->token;
        }
    }

    /**
     * @param array $options
     * @return array|null
     */
    public function getMedia(array $options = [])
    {
        if ($this->token === null) {
            return null;
        }

        $cache = Craft::$app->getCache();

        $cacheKey = ArrayHelper::merge([
            'key' => 'instagram',
            'userId' => self::USER_ID,
        ], $options);

        $cacheDuration = ArrayHelper::getValue($options, 'cache', 300);

        $data = $cache->get($cacheKey);

        if (!$data || $cacheDuration === false) {
            $client = Craft::createGuzzleClient([
                'base_uri' => Plugin::GRAPH_ENDPOINT,
            ]);

            try {
                $request = $client->get(self::USER_ID . '/media', [
                    'query' => [
                        'access_token' => $this->token,
                        'fields' => implode(',', self::MEDIA_FIELDS),
                        'after' => ArrayHelper::getValue($options, 'after'),
                        'before' => ArrayHelper::getValue($options, 'before'),
                        'limit' => ArrayHelper::getValue($options, 'limit'),
                    ],
                    'http_errors' => false,
                    'timeout' => 30,
                    'connect_timeout' => 30,
                ]);

                $data = Json::decodeIfJson($request->getBody());

                if (ArrayHelper::keyExists('error', $data)) {
                    Craft::error($data, __METHOD__);
                } elseif ($cacheDuration !== false) {
                    $dependency = new TagDependency(['tags' => self::CACHE_TAG]);
                    $cache->set($cacheKey, $data, $cacheDuration, $dependency);
                }
            } catch (RequestException $e) {
                Craft::error($e->getMessage(), __METHOD__);
            }
        }

        return $this->processData($data);
    }

    public function processData($data)
    {
        if (!is_array($data) || ArrayHelper::keyExists('error', $data)) {
            return null;
        }

        $mediaItems = [];

        foreach ($data['data'] as $media) {
            $model = new MediaModel([
                'caption' => $media['caption'] ?? '',
                'id' => $media['id'],
                'mediaType' => $media['media_type'],
                'mediaUrl' => $media['media_url'],
                'permalink' => $media['permalink'],
                'thumbnailUrl' => ArrayHelper::getValue($media, 'thumbnail_url'),
                'timestamp' => $media['timestamp'],
                'username' => $media['username'],
            ]);

            $mediaItems[] = $model;
        }

        return [
            'media' => $mediaItems,
            'before' => ArrayHelper::getValue($data, ['paging', 'cursors', 'before']),
            'after' => ArrayHelper::getValue($data, ['paging', 'cursors', 'after']),
        ];
    }

    /**
     * Invalidate cached data.
     */
    public function invalidateCache()
    {
        $cache = Craft::$app->getCache();

        TagDependency::invalidate($cache, self::CACHE_TAG);
    }
}
