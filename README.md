## Faceted Category Navigation for Craft CMS

### Requirements

* This plugin requires PHP 7 or higher.
* This plugin has been tested with Craft CMS 2.6.2991

### Overview

This plugin assists in creating faceted navigation across single or multiple
Craft CMS category groups.

Imagine a Plant Nursery website that requires filtering of plant types, soil
types, and light exposures. Ultimately we want to obtain a URL structure like:

```
category_group/category_1|category_2/category_group2/category_3
```

In our example valid URLs include:

```
/catalogue/sun/full-sun/soil/sandy-volcanic
/catalogue/soil/sandy-volcanic|clay-based-loams
/catalogue/plants/shrubs/sun/partial-shade|full-sun/soil/sandy-volcanic
```

### Craft Setup

1. Created 3 category groups with the handles of `plants`, `soil` and `sun`.
Assign these categories to a field, and to the `catalogue` section.

  Each have a maximum level set of 1, which is a current limitation of the plugin.

  **Important**: avoid identical category group handles and slugs (e.g. `promo/promo`)

2. Create a template folder with an index in `/craft/templates/catalogue` for the front-end.

3. In `/craft/config/routes.php`, add `'catalogue/(.*?)' => 'catalogue/index'`
to route all requests to the `/catalogue` template, regardless of URL segments.

4. Add some entries and classify them using the available category groups for testing.

5. Install the plugin and open `/craft/templates/catalogue/index`

### Craft Plugin Usage

Calling `craft.facetedNavigation.getNavigation` allows you to render your
navigation sets and output current filters, as well as build a parameter for
your main `craft.entries` call when outputting your entries.

So start with a basic set tag, and pass an array of your category group
*handles* to the plugin, and also set a couple of variables that will come in
handy later.

	{% set navItems = craft.facetedNavigation.getNavigation(['plants', 'sun', 'soil']) %}
	{% set relatedTo = '' %}
	{% set params = {section: 'catalogue'} %}

Now that `navItems` is set, you can output your navigation group or groups if
more than one category group is set.

	{% for catGroup in navItems.categoryGroups %}
	<h4>{{ catGroup.name }}</h4>
	<ul>
		{% for cat in attribute(navItems.categories, catGroup.handle) %}
			<li{% if cat.active %} class="active"{% endif %}>
				<a href="{{ url('catalogue'~cat.url.add) }}" class="add">{{ cat.title}}</a>
				{% if cat.active %} <a href="{{ url('catalogue'~cat.url.remove) }}" class="remove" title="Remove this filter">&times;</a> {% endif %}
			</li>
		{% endfor %}
	</ul>
	{% endfor %}

We also want to output a breadcrumb or tag list showing the current, active filters, if any, and allow for those to be removed by the user, and while we're building
that we'll set up the parameters for our main `craft.entries` tag.

	{% if navItems.activeCategories|length %}
		{% set relatedTo = ['and'] %}
		<nav>
			<h3>Browsing items filed under:</h3>
			{% for category in navItems.activeCategories %}
				{% set relatedTo = relatedTo|merge([category.model]) %}
				<a href="{{ url('catalogue'~category.url.remove) }}" title="Remove this filter">{{ category.title }} <span>&times;</span></a>
			{% endfor %}
			{% set params = params|merge({relatedTo: relatedTo}) %}
		</nav>
	{% endif %}

Now that we've got our parameters set, we can call our main `craft.entries` tag:

	{% paginate craft.entries(params).limit(12) as entries %}

		{% for entry in entries %}
			<article>
				<h2>{{ entry.title }}</h2>
				...
			</article>
		{% endfor %}

		{% include '_partials/_pagination' %}

	{% endpaginate %}

### Changelog

#### 1.1.0 - 09 Oct 2017

* Added relative category count to active category data
* Miscellaneous tweaks and fixes
* Added phpDocumentor docblocks, inline comments, LICENSE, CONTRIBUTING.md, composer.json, .editorconfig

#### 1.0.0 - 19 Dec 2014

* forked from [iainurquhart/FacetedNav_CraftPlugin](https://github.com/iainurquhart/FacetedNav_CraftPlugin/tree/e7f58e890e45190f70a2874546a1389e0d559a3d)

### License

This Craft plugin is released under the permissive [MIT](https://github.com/wearechilli/facetednavigation/LICENSE) license.
