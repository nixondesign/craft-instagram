# Instagram

This plugin provides access to the [Instagram Basic Display API](https://developers.facebook.com/docs/instagram-basic-display-api).

## Authorising Instagram

In order to display an Instagram user's media you must first authorise the plugin to access a users Instagram account. Authorisation is handled on a per site basis, that is to say each site has one authorised user.

Before you can authorise a user you must first set up an Instagram app, this is the app you'll be using to authorise users and make API calls. Once set up you will require the App ID and Secret.

Enter the app ID and secret these into the authorisation form and submit. You'll then be asked to log in to your Instagram account and provide the necessary permissions. The Instagram account you log in to will be the account which to pull media from. Once you grant permissions you'll be redirected back to the plugins authorisation page where it will then request an access token from the Instagram API.

### Refreshing Access Tokens

This plugin uses [long-lived access tokens](https://developers.facebook.com/docs/instagram-basic-display-api/guides/long-lived-access-tokens) which are valid for 60 days. To avoid having to reauthorise every 60 days these tokens can be refreshed to increase their life by another 60 days. Access tokens can be refreshed via the plugin using the `instagram/tokens/refresh` CLI command. This command should be run periodically via a crontab.

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
