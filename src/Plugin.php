<?php

namespace nixondesign\instagram;

use nixondesign\instagram\services\Auth;
use nixondesign\instagram\services\Media;

use Craft;
use craft\events\RegisterCacheOptionsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\UserPermissions;
use craft\utilities\ClearCaches;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;

/**
 * Instagram plugin
 *
 * @property Auth $auth The auth service
 * @property Media $media The media service
 *
 * @author Nixon Design Ltd
 * @since 1.0
 */
class Plugin extends \craft\base\Plugin
{
    const API_ENDPOINT = 'https://api.instagram.com';

    const GRAPH_ENDPOINT = 'https://graph.instagram.com';

    /**
     * @var bool
     */
    public static $devMode;

    /**
     * @inheritdoc
     */
    public bool $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public bool $hasCpSection = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        self::$devMode = Craft::$app->getConfig()->getGeneral()->devMode;

        $this->setComponents([
            'auth' => Auth::class,
            'media' => Media::class,
        ]);

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules['instagram'] = ['route' => 'instagram/auth'];
            $event->rules['instagram/<siteHandle:{handle}>'] = ['route' => 'instagram/auth'];
            $event->rules['instagram/auth'] = 'instagram/auth/authenticate';
            $event->rules['instagram/oauth-redirect'] = 'instagram/auth/get-token';
        });

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions[] = [
                'heading' => Craft::t('instagram', 'Instagram'),
                'permissions' => [
                    'instagram-auth' => [ 'label' => Craft::t('instagram', 'authorisePermissionLabel') ],
                    'instagram-removeToken' => [ 'label' => Craft::t('instagram', 'removePermissionLabel') ],
                    'instagram-refreshToken' => [ 'label' => Craft::t('instagram', 'refreshPermissionLabel') ],
                ],
            ];
        });

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $variable = $event->sender;

            $variable->set('instagram', Variable::class);
        });

        Event::on(ClearCaches::class, ClearCaches::EVENT_REGISTER_CACHE_OPTIONS, function (RegisterCacheOptionsEvent $event) {
            $event->options[] = [
                'key' => 'instagram',
                'label' => Craft::t('instagram', 'feedDataCacheLabel'),
                'action' => [$this->getMedia(), 'invalidateCache'],
            ];
        });
    }

    /**
     * Gets the oAuth redirect URL.
     *
     * @return string The oAuth redirect URL
     */
    public static function getOAuthRedirectUrl(): string
    {
        return UrlHelper::cpUrl('instagram/oauth-redirect');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse(): mixed
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('instagram'));
    }

    /**
     * Returns the media service.
     *
     * @return Media The media service.
     */
    public function getMedia()
    {
        return $this->get('media');
    }

    /**
     * Returns the tokens service.
     *
     * @return Auth The tokens service.
     */
    public function getAuth()
    {
        return $this->get('auth');
    }
}
