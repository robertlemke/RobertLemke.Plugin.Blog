# Neos CMS Blog Plugin

This plugin provides a node-based plugin for Neos websites.

Note: Although this package is in use (for example on robertlemke.com) it is not a full-fledged blogging solution.

## Quick start

* Include the Plugin's route definitions to your `Routes.yaml` file, just like

```yaml
-
  name: 'RobertLemkeBlogPlugin'
  uriPattern: '<RobertLemkeBlogPluginSubroutes>'
  subRoutes:
    RobertLemkeBlogPluginSubroutes:
      package: RobertLemke.Plugin.Blog
```

* add the plugin content element "Blog Post Overview" to the position of your choice.

## Comment notifications

As soon as the notifications.to.email setting is configured and neos/swiftmailer is installed, a notification
will be sent whenever a comment is submitted.

## Akismet spam checking

If you configure the Akismet package comments will be checked for being spam and marked as such.

## RSS feed

* add a page to serve the feed below your posts container node, it can be empty and should be hidden in menus

* add this to your TS (configuration shows default values):

```
xml = RobertLemke.Plugin.Blog:Feed {
  feedTitle = 'The Neos Blog'
  feedDescription = 'A great, new - yet unconfigured - blog powered by Neos'
  feedUri = ''
  includeContent = ${false}
}
```

Now when you visit the "feed node" and use xml instead of html in the URL, you should see an XML feed os the
blog.
