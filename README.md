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
