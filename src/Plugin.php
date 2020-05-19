<?php

namespace nixon\instagram;

use nixon\instagram\services\Feeds;
use nixon\instagram\services\Tokens;

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
 * @property Feeds $feeds The feeds service
 * @property Tokens $tokens The tokens service
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
    public $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public $hasCpSection = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        self::$devMode = Craft::$app->getConfig()->getGeneral()->devMode;

        $this->setComponents([
            'tokens' => Tokens::class,
            'feeds' => Feeds::class,
        ]);

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules['instagram'] = ['route' => 'instagram/tokens'];
            $event->rules['instagram/token'] = 'instagram/tokens/token';
            $event->rules['instagram/<siteHandle:{handle}>'] = ['route' => 'instagram/tokens'];
            $event->rules['instagram/auth'] = 'instagram/tokens/authenticate-user';
        });

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions['Instagram'] = [
                'instagram-auth' => [ 'label' => Craft::t('instagram', 'authorisePermissionLabel') ],
                'instagram-removeToken' => [ 'label' => Craft::t('instagram', 'removePermissionLabel') ],
                'instagram-refreshToken' => [ 'label' => Craft::t('instagram', 'refreshPermissionLabel') ],
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
                'action' => [$this->getFeeds(), 'invalidateCache'],
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
        return UrlHelper::cpUrl('instagram/token');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse()
    {
        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('instagram'));
    }

    /**
     * Returns the feeds service.
     *
     * @return Feeds The feeds service.
     */
    public function getFeeds()
    {
        return $this->get('feeds');
    }

    /**
     * Returns the tokens service.
     *
     * @return Tokens The tokens service.
     */
    public function getTokens()
    {
        return $this->get('tokens');
    }
}
