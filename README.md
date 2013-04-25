TYPO3 Neos Blog Plugin
======================

This plugin provides a node-based plugin for TYPO3 Neos websites.

Note: this package is still experimental and may change heavily in the near future.

Quick start
-----------

* Include the Plugin's route definitions to your `Routes.yaml` file, just like

```yaml
-
  name: 'RobertLemkeBlogPlugin'
  uriPattern: '<RobertLemkeBlogPluginSubroutes>'
  subRoutes:
    RobertLemkeBlogPlugin:
      package: RobertLemke.Plugin.Blog
```

* Include the plugin's TypoScript definitions to your own one's (located in, for
example, `Packages/Sites/Your.Site/Resources/Private/TypoScripts/Library/ContentElements.ts2`,
with:

```
include: resource://RobertLemke.Plugin.Blog/Private/TypoScripts/Library/NodeTypes.ts2
```

* Add this to your TS, assuming your content should appear in section "main":

```
blogPostPage < page {
	body.sections.main = RobertLemke.Plugin.Blog:Post
}

root.blogPostMatcher {
	condition = ${q(node).is('[instanceof RobertLemke.Plugin.Blog:Post]')}
	renderPath = 'blogPostPage'
}
```

* add the plugin content element "Blog Post Overview" to the position of your choice.