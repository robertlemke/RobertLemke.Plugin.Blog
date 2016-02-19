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

As soon as the notifications.to.email setting is configured and TYPO3.SwiftMailer is installed, a notification
will be sent whenever a comment is submitted.

## Akismet spam checking

If you configure the Akismet package comments will be checked for being spam and marked as such.
