# Neos CMS Blog Plugin

This plugin provides a node-based plugin for Neos websites.

Note: Although this package is in use (for example on robertlemke.com) it is not a full-fledged blogging solution.

## Quick start

* add the plugin content element "Blog Post Overview" to the position of your choice.

## Comment notifications

As soon as the notifications.to.email setting is configured and neos/symfonymailer is installed, a notification
will be sent whenever a comment is submitted.

You can configure the email address to which the notifications are sent by adding the following to your `Settings.yaml`:

```yaml
RobertLemke:
  Plugin:
    Blog:
      notifications:
        to:
          email: 'jon@doe.org'
          name: 'You Name'
```

## Akismet spam checking

If you configure the Akismet package comments will be checked for being spam and marked as such.

## RSS feed

The RSS feed is available at `/rss.xml` by default. You can change the URL by adjusting the route configuration.
To add the RSS feed to the head of your page, you can add a Fusion component like the following to your page.
This is just an example, you can adjust it to your needs.

```
Neos.Neos:Page {
	head {
		metadata = Example.Site:Integration.Components.MetaTags
	}
}
```

```
prototype(Example.Site:Integration.Components.MetaTags) < prototype(Neos.Fusion:Component) {
    rssUri = Neos.Neos:NodeUri {
        node = ${rootSite || site}
        absolute = true
        @process.append = ${(String.endsWith(value, '/') ? value : value + '/') + 'rss.xml'}
    }

    renderer = afx`
        <link rel="alternate" type="application/rss+xml" title="RSS-Feed" href={props.rssUri}/>
    `
}

```
