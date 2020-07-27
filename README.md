# Instagram

This plugin provides easy access to the [Instagram Basic Display API](https://developers.facebook.com/docs/instagram-basic-display-api) for [Craft CMS](https://craftcms.com/).

**Features:**

- Twig & JSON: Feeds are accessible both in Twig and as JSON via action urls.
- Multisite support: Apps are authenticated per site allowing per site media feeds.
- Easy token management: Tokens can easily be refreshed via the control panel or CLI.

## Authorising Instagram

Before you can authorise the plugin you must first set up a Facebook app, this is the app you'll be using to authorise users and make API calls. Once set up you will require the App ID and App Secret. To set up your app, follow steps 1 to 3 in the [offical getting started guide](https://developers.facebook.com/docs/instagram-basic-display-api/getting-started). When asked for your "Valid OAuth Redirect URIs", use the URL found in the plugin settings page. If using multisite you'll need to create test users for each account you want to authorise.

### Plugin Setup

1. First either make sure you are logged into the Instgram account you want to authenticate or are logged out completely.
2. Navigate to the plugin settings page, if running Craft multisite switch to the appropriate site.
3. Enter the App ID and App Secret, found under App Dashboard > Products > Instagram > Basic Display. These can be set to environment variables.
4. Click "Authenticate" where you'll be taken to Instagram to authorise the plugin. Once authorised you will be redirected back to the plugin page.

### Refreshing Access Tokens

This plugin uses [long-lived access tokens](https://developers.facebook.com/docs/instagram-basic-display-api/guides/long-lived-access-tokens) which are valid for 60 days. These tokens can be refreshed to increase their life by another 60 days using the `instagram/tokens/refresh` CLI command. This command could be run periodically via cron.

```
0 0 1 * * /craft instagram/tokens/refresh
```

Tokens can also be refreshed via the control panel.

## Displaying a users media

Fetching the users can be handled in two ways, via Twig or using JSON returned from an action URL.

### Twig

A users media can be displayed using the `getMedia()` method. This method accepts an optional [options](#options) parameter.

```twig
{% set feed = craft.instagram.getMedia({
  limit: 20
}) %}

{% if feed %}
  {% for media in feed.media %}
    {{ media.getImg() }}
  {% endfor %}
{% endif %}
```

#### Media Object

| Property     | Description                                                  |
| :----------- | :----------------------------------------------------------- |
| id           | ID.                                                          |
| caption      | Caption text. Not returnable for Media in albums             |
| username     | Owner's username                                             |
| timestamp    | Publish date                                                 |
| permalink    | Permanent URL                                                |
| mediaUrl     | URL                                                          |
| mediaType    | Type of media. Can be IMAGE, VIDEO, or CAROUSEL_ALBUM        |
| thumbnailUrl | thumbnail image URL. Only available on video Media           |
| getUrl()     | Either the mediaUrl or thumbnailUrl depending on media type  |
| getImg()     | Returns an image element                                     |

#### Paging

In addition to the `media` property, `getMedia` also returns `before` and `after` properties for [pagination](#pagination).

### JSON

The media feed is available as JSON via the `instagram/media/fetch` action URL. [Options](#options) can be passed as query parameters.

```
instagram/media/fetch?limit=1
```

```json
{
  "media": [
    {
      "id": "",
      "caption": "",
      "username": "",
      "timestamp": "",
      "permalink": "",
      "mediaUrl": "",
      "mediaType": "",
      "thumbnailUrl": ""
    }
  ],
  "before": "",
  "after": ""
}
```

### Pagination

Pagination can be implemented by using the `before` and `after` properties sent along with the `media` property.

```json
{
  "media": [],
  "before": "",
  "after": ""
}
```

These can be then sent as options with the next request.

**Twig**

```twig
craft.instagram.getMedia({
  after: "xxxxxxxxxxxxxxxxxxx"
})
```

**Action URL**

```
instagram/media/fetch?after=xxxxxxxxxxxxxxxxxxx
```

### Options

Both the Twig Variable and JSON endpoint accept the following options:

| Option | Description                                                            | Default |
| :----- | :--------------------------------------------------------------------- | :------ |
| after  | Unique marker which to fetch media after                               | null    |
| before | Unique marker which to fetch media before                              | null    |
| cache  | The duration to cache data for in seconds, set to false for no caching | 300     |
| limit  | The number of media items to fetch, if null uses Instagram's default   | null    |
