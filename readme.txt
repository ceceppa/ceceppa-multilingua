=== Ceceppa Multilingua ===
Contributors: ceceppa
Tags: multilingual, language, admin, tinymce, bilingual, widget, switcher, i18n, l10n, multilanguage, professional, translation, service, human, qtranslate, wpml, ztranslate, xtranslate, international, .mo file, .po file, localization, widget, post
Requires at least: 3.4.1
Tested up to: 4.2
Stable tag: 1.5.17
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Donate Link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=G22CM5RA4G4KG

Adds userfriendly multilingual content management and translation support into Wordpress.

== Description ==

I created Ceceppa Multilingua to let Wordpress have an easy to use interface for managing a fully multilingual web site.
With "Ceceppa Multilingua" you can write your posts and pages in multiple language. Here are some features:

= Features =

- Quick Edit mode. Allow you to edit the main content and its translations easily from one page/
- Separated posts and pages for each languages, so you can use different SEO and url for each languages.
- Translate your theme ( Plugin let you translate strings and will generate the .mo file for wordpress )
- URLs pretty and SEO-friendly. ?lang=en, /en/foo/ or en.yoursite.com
- Translate widgets title.
- Filter widgets by language
- Translate Site Title / Tagline
- One-Click-Switching between the languages
- One-Click-Switching between the translations
- Category link translation
- Different menu for each language.
- Add flags to menu
- Customize "Navigation label" for each language
- Group/Ungroup comments for each post languages.
- Show notice when the post/page that user is viewing is available, based on the information provided by the browser, in their its language
- Least Read Posts, Most Commented, Most Read Posts can show only the posts in user selected language
- Change wordpress locale according to current language, useful for localized themes
- Show the list flag of available languages on top or bottom of page/post
- Plugin works also with custom post types :)
- Redirects the browser depending on the user's language.
- Different post filter method
- Filter search in according to current language
- Allow you to translate your own custom post type slugs
- Compatible with wpml-config.xml ( experimental )

= Widgets =

- "CML: Language Chooser" - Show the list of available languages
- "CML: Recent Posts" - The most recent posts on your site
- "CML: Text" - You can write arbitrary text or HTML separately for each language

= Addons =
- [Ceceppa Multilingua support to Customizr](http://www.alessandrosenese.eu/en/ceceppa-multilingua/ceceppa-multilingua-for-customizr)
- [Ceceppa Multilingua support to Woocommerce](http://www.alessandrosenese.eu/en/ceceppa-multilingua/ceceppa-multilingua-for-woocommerce)

= 3rd part compatible plugins =
- Wordpress SEO by YOAST
- All in one SEO pack
- Google XML Sitemaps

= Let's start =
Ceceppa Multilingua supports infinite language, which can be easily added/modified/deleted via the comfortable Configuration Page.
All you need to do is activate the plugin, configure categories and start writing the content!

= About =
For more Information visit the [Plugin Homepage](http://www.alessandrosenese.eu/portfolio/ceceppa-multilingua/)
[Setting up a WordPress multilingual site with Ceceppa Multilingua](http://www.alessandrosenese.eu/en/ceceppa-multilingua/documentation)
[Contribute](http://www.alessandrosenese.eu/en/ceceppa-multilingua/contribute)
[Developers](http://www.alessandrosenese.eu/en/ceceppa-multilingua/developers)

= Demo =
[Plugin demo](https://www.youtube.com/watch?v=QoF8IQCZccw)

= Flags =
Flags directory are downloaded from [Flags](http://blog.worldofemotions.com/danilka/)

= Icons =
Some icons from [Icons](http://www.iconmonstr.com/)

= jQuery plugins =
Tooltip plugin for Jquery [Tipsy](http://onehackoranother.com/projects/jquery/tipsy/)

= Php gettext =
Php-gettext by Danilo Shegan [php-gettext] https://launchpad.net/php-gettext/
Pgettext by Ruben Nijveld

== Installation ==

For more detailed instructions, take a look at the [Installation Guide](http://www.alessandrosenese.eu/en/ceceppa-multilingua/installation/)

Installation of this plugin is fairly easy:

1. Download the plugin from wordpress.
1. Extract all the files.
1. Upload everything (keeping the directory structure) to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Manage desidered languages

== Frequently Asked Questions ==

= The home page is randomly change when a static one is set =
For some template happens that static page is changed with a random one.
If that happens just go to "Ceceppa Multilingua" -> "Settings" -> "Advanced" tab and uncheck the "Static page" option

= Is it possible to not have a slug for the main language? =
Yes, it is :).
In "Ceceppa Multilingua" -> "Settings" set the option "Detect browser language:" to "Do Nothing".

= Where can I find full shortcode list =
After installing the plugin you can find full shortcode list in "Ceceppa Multilingua" -> "Shortcode" page.

= Can I also translate the Widget Text =

Yes, you can translate text in the widget, page or post using the shortcode cml_text.
The syntax is:

[cml_text lang1="text..." lang2="text..." ....]

Replace lang1 and lang2 with your languages slug, for example:

[cml_text it="Ciao" en="Hello" epo="Saluton"]

For complete shortcode list visit: [Shortcode] (http://www.alessandrosenese.eu/it/pillole/wp-guide/ceceppa-multilingua-configurare-e-utilizzare-il-plugin/4/)

= How can I show flags for switch between languages =

  1) editing your theme file and use the function:

     <?php cml_show_flags() ?>

  2) using the widget "CML Widget: Language Chooser"

  3) enabling option in "Ceceppa Multilingua" -> "Settings" page

= What is the function to get current language =

  The function is:

     cml_get_current_language();

  This function return an object and Its fields are:

    *) id           - id of language
    *) cml_default  - 1 if it is the default language
    *) cml_flag     - name of flag
    *) cml_language - name of the language
    *) cml_language_slug - slug of the language
    *) cml_locale        - wordpress locale

= Can I show flags on my website without using widget? =

Yes, you can:

  1) Add float div to website and customize look via css
  2) Append flag to html element
  3) Add flags to menu

= Can I translate the "Site Title" and/or "Tagline" =

Yes, you can translate them in "Ceceppa Multilingua" -> "Site Title/Tagline" page.

= Can I customize the flags? =

Yes, you can but don't store your own flags in the plugin directory, or you lose them when update the plugin.
Store your own flags in:
*) "wp-content/upload/ceceppaml/tiny" - tiny size
*) "wp-content/upload/ceceppaml/small" - small size

If the directory "ceceppaml" doesn't exists, create it

= How to configure the plugin, and support page =

The FAQ is available at the [Plugin Homepage](http://www.alessandrosenese.eu/en/ceceppa-multilingua/documentation)

For Problems visits the [Support page](http://www.alessandrosenese.eu/en/ceceppa-multilingua/documentation)

== Screenshots ==

1. Language configuration
2. Quick edit mode
3. List of all posts with their translations
4. Translate widget's title
5. Filter widget by language
6. Plugin configuration
7. Link posts
8. Menus configuration
9. Translate your theme

== Changelog ==

= 1.5.17 =
* Fixed wrong link id post edit, when selecting Publish Translation
* Loaded script in the footer

= 1.5.16 =
* Fixed Warning

= 1.5.14 =
* Improved woocommerce compatibility
* Fixed adding items on each submenu
* Fixed redirect loop
* Fixed double redirect, after added language slug for the homepage
* Fixed uncheckable option in the settings page
* Fixed issue with cml_show_flags returning wrong links

= 1.5.12 =
* Added tag translation in wp_title

= 1.5.11 =
* Added missing file

= 1.5.10 =
* Fixed table cml category structure
* Added a fix when the field cml_cat_description is not created by the updating script
* Fixed issue when assign language in "quick edit"
* Auto disabled extra number removal when the post slug doesn't match the original one
* Fixed various bugs

= 1.5.9 =
* Fixed table structure

= 1.5.8 =
* Fixed Warning on utils.php

= 1.5.7 =
* Fixed Call to undefined function in compatibility.php
* Added ability to translate term description
* Fixed language detect on ajax call, and so with woocommerce permalinks

= 1.5.6 =
* Try to fix the 500 Internal Server Error
* Added auto backup on plugin update
* Fixed static page changing with random One

= 1.5.5 =
* Added ability to translate category slug as well
* Added CMLTaxonomies api
* Updated documentation
* Added code to fix the 500 Internal Server Error
* Fixed custom menu attribute for cpt

= 1.5.4 =
* Security update
* Added the missing Function CMLFrontend::filter_archives
* Added Chinese Singapore, Chinese - HONG KONG, Chinese - TAIWAN translation, thanks to Henry Siu

= 1.5.3 =
* Added Chinese translation. Thanks to Henry Siu
* Fixed issue when using the function get_term_by( 'slug' ), now wp will return the WP_Term object

= 1.5.2 =
* Fixed issue with woocommerce addon and with posts sets as "unique"

= 1.5.1 =
* Fixed compatibility with ceceppa's woocommerce addon
* Security update

= 1.5.0 =
* Added function icl_get_languages
* Added serbian thanks to ( http://www.webhostinghub.com )
* Added support to hreflang tag
* Fixed feed when "Do nothing" filter is selected
* Added settings backup/restore
* Fixed sitemap generation
* various bug fixes
* Now you can easily translate any custom post slug
* forced redirect for translated post/page permalink
* Added compatibility with WPML icl_get_languages function
* Fixed issue with menu custom navigation label
* Fixed category translation issue when the names containing html special chars
* Added possibility to search in all rows in my translations pages
* Fixed issue with YOAST 2.x
* Fixed issue with the cml_frontend_hide_translations_for_tags function

= 1.4.37 =
* Added warning in languages page if all the changes are not saved yet

= 1.4.36 =
* Fixed "Enable Language filtering" option
* Fixed Widget filtering
* Fixed 404 when wp add extra "-##" to category slug
* Fixed "Float and append" flag styles
* Fixed language "combo" switcher for touch devices

= 1.4.35 =
* Fixed "Post data" box in pages

= 1.4.34 =
* Fixex language detection on first install

= 1.4.33 =
* Fixed issue with HTTPS
* Added option to easily disable language filtering ( dev time: 1h 30m )

= 1.4.32 =
* Fixed issue with category

= 1.4.31 =
* Fixed wrong link translation when enabled the "Ignore for default language" option

= 1.4.30 =
* Fixed the "Editing comments resets post language" issue
* Fixed bug in "Translate theme" page
* Fixed feed issue

= 1.4.29 =
* Fixed issue with WooCommerce checkout page link
* Added Norwegian translation ( thanks to Kurt-Håkon )

= 1.4.28 =
* Fixed sitemap with yoast plugin

= 1.4.27 =
* Fixed "All languages" bug

= 1.4.26 =
* Fixed redirect mode
* Fixed loop when Ignore for default language is enabled
* Added new "Filter none" mode for One page themes
* Fixed various bugs

= 1.4.24 =
* remove page_front updating

= 1.4.23 =
* Fixed "mb_detect_encoding" not found error
* Fixed Widget titles translation

= 1.4.22 =
* Added warning when upgrading from CML 1.4 was failed
* Fixed search result when using static page

= 1.4.21 =
* fixed translation when edit category

= 1.4.20 =
* Fixed post language/permalink when using not enabled language

= 1.4.19 =
* Fixed Warning on get_translated_title function
* Fixed close tag in cml_show_flags function

= 1.4.18 =
* Added missing files
* Fixed Italian translation

= 1.4.17 =
* Fixed preview post/page when using static homepage for non default language
* Added "Fuzzy" field in "Theme/Plugin" translation
* Fixed few bugs
* Added new hook for Woocommerce support
* Now you can translate "Alternative Text" and media title
* Replaced mo generation function to avoid timeout error
* Fixed bug in "Widget titles" page

 = 1.4.16 =
* Fixed preview post/page when using static homepage
* Now will be displayed correct flag after "quick edit" update

= 1.4.15 =
* Added "current $type" in dropdown translations list
* Added quick shortcode [_##_] ( Ex: [_en_]Text to show[/_en_] )
* Updated shortcode documentation

= 1.4.14 =
* Category translation in backend when using non default language
* Fixed "Yoast" and "All in one SEO" compatibility notice

= 1.4.13 =
* Queries optimized
* Added "is_unique" function to CMLPost class
* Fixed "page" link

= 1.4.12 =
* Added new option in "Actions" tab to disable category url translation

= 1.4.11 =
* Fixed "save post relations"
* The plugin will automatically copy tags from original post when click on "+" or "pencil" link
* Added IE 9 compatibility
* Fixed wrong page link in menu

= 1.4.10 =
* Fixed quick edit
* Fixed post relations
* Added timeout error in "Translate your theme" page

= 1.4.9 =
* Optimized query requests
* Fixed "Ceceppa Multilingua in your languages" in Firefox
* Added "Addons" tab

= 1.4.8 =
* Fixed "syntax error"

= 1.4.7 =
* Improved parser
* Fixed Italian translation

= 1.4.6 =
* Fixed theme translation layout
* Fixed compatibility with php < 5.3
* Fixed homepage url when using "forced" language

= 1.4.5 =
* Fixed Georgian flag
* Fixed language change in "quick edit"
* Fixed custom "Navigation label"
* Fixed dropdown menu style with Customizr
* Improved "parser" to translate "Theme" and "Plugin"

= 1.4.4 =
* Fixed issue when category slug is different from name
* Fixed custom menu url item for non default language
* Fixed "Force language items"
* Fixed post relations with more 2 languages
* Fixed "remove extra -##" for all url mode

= 1.4.3 =
* Fixed url mode "?lang"
* Fixed Widget filter
* Fixed "Warning" with YOAST plugin

= 1.4.2 =
* Added missing file "php-compatibility.php"

= 1.4.1 =
* Fixed compatibility with php <= 5.3.0

= 1.4.0 =
* New interface
* Added help in Settings and Show flags pages
* Now you can translate widget title from widget container itself
* Now you can filter widgets by language
* In menu, Now you can assign different "Navigation label" and "Title attribute" for each languages
* In menu, for Link item, you can set different url for each language
* Added uninstall tab in "Settings" page to uninstall all plugin data
* Added new flags style "Combo"
* Now you can group comments also for pages
* You can translate the plugin in your language
* The plugin will automatically set categories or page parent from original post/page.
* Added compatibility with "Google XML Sitemap"
* Added compatibility with "Wordpress SEO by YOAST"
* Added compatibility with "All in One SEO pack"
* Added experimental support to "wpml-config.xml" ( for plugins and themes )

= 1.3.68 =
* Fixed "Filter posts"

= 1.3.66 =
* Fixed "Warning"
* You can "Remove Pre-Path Mode for default language"

= 1.3.65 =
* Fixed some bugs

= 1.3.61 =
* Archives widget now is filtered by language

= 1.3.60 =
* Now page language is stored correctly
* Added "Language slug" as "show flag" method

= 1.3.59 =
* Fixed "loop" on homepage when use "static page"

= 1.3.58 =
* Fixed "Warning"

= 1.3.57 =
* Fixed "redirection" error
* Now translations can use same permalink, "-##" will be removed :)
* Fixed "search" query

= 1.3.56 =
* Fixed "migration"

= 1.3.52 =
* Fixed message "update required"

= 1.3.50 =
* Fixed Warning, added migration message

= 1.3.49 =
* Now you can link posts in "Quick edit" box

= 1.3.48 =
* revert to 1.3.46

= 1.3.47 =
* Fixed custom date format.
  Now plugin filter all WP_Query

= 1.3.46 =
* Fixed image path when add flags in submenu

= 1.3.45 =
* Fixed "500 Internal error" when no default language choosed

= 1.3.43 =
* Fixed "Filter posts by language"

= 1.3.40 =
* Fixed warning

= 1.3.35 =
* Added support for different date format for each language

= 1.3.33 =
* Fixed warning filter_get_pages

= 1.3.31 =
* Fixed flags in_the_loop

= 1.3.28 =
* Added new filter method: "Hide empty translations of posts and show in default language"

= 1.3.26 =
* Added support for right to left languages.

= 1.3.23 =
* Fixed bug with page links

= 1.3.22 =
* Fixed body class "home" when use static page

= 1.3.21 =
* Fixed activation hook

= 1.3.19 =
* Fixed bug for children pages
* Added "All language exclued current" to menu meta box
* Added new redirect mode: "Automatically redirects the default language."

= 1.3.17 =
* Fixed bug whit cml_show_available_langs

= 1.3.16 =
* Now you can add flags to menu directly in the page "Aspect" -> "Menu"
* You can show flags also in the loop
* Fixed bug with category and url mode: ?lang=##
* Also the link of the custon post will be "translated"...

= 1.3.15 =
* Fixed warning in widget "chooser"

= 1.3.13 =
* Fixed bug "show posts only in current language"

= 1.3.12 =
* Revert to 1.3.10

= 1.3.11 =
* Fixed problem with duplicated "menus"
* Fixed various bugs

= 1.3.10 =
* Remove print_r :O ( sorry )

= 1.3.9 =
* Fixed change menu when switch between languages

= 1.3.8 =
* Improved "Translate your theme"
* Fixed problem with duplicated "menus"

= 1.3.7 =
* Improved "Translate your theme"
* Added the option for display flags only if translation exists
* Added shortcode/function cml_other_langs_available

= 1.3.6 =
* Fixed widget "CML Language Chooser"

= 1.3.5 =
* Fixed warning

= 1.3.4 =
* Fixed problem with shortcode [cml_text]

= 1.3.3 =
* Added Farsi flag
* Now you can add custom language and flag

= 1.3.2 =
* Improved translation of the themes
* Fixed error in "My translations" if Php < 5.3.0
* Added warning in "Translate your theme if Php < 5.3.0"

= 1.3.1 =
* Improved translation of the themes

= 1.3.0 =
* Now you can translate your theme with Ceceppa Multilingua
* Fixed minor bugs
* Improved help
* Moved tips in help tab

= 1.2.22 =
* Added Url Modification mode: NONE

= 1.2.21 =
* Fixed "redirect loop"

= 1.2.20 =
* Now you can disable the translations of menu items

= 1.2.19 =
* CML: Text Widget now support also shortcodes

= 1.2.18 =
* Translate post link in menu

= 1.2.16 =
* Translate Site Title / Tagline

= 1.2.11 =
* Added border to the active language on the "all posts" page
* Added checkbox for show also posts withouth translations in "all posts"

= 1.2.8 =
* Fixed pagination link
* Fixed same warnings

= 1.2.5 =
* Fixed PRE_PATH mode.
* Code optimization
* Fixed problem with next and previous post

= 1.2.4 =
* Fixed fatal error "wp_rewrite_rules()"

= 1.2.1 =
* Fixed setlocale

= 1.2.0 =
* Plugin automatically try to download language file when you add new language.
* Locale is detected correctly
* Plugin use wordpress localization for widget titles.
* Improved documetation: Added "Functions" tab in "Shortcode & Functions" page.
* Now title of category will also be translate in "/category/" page
* Fixed comments count when choose to group them
* Improved ui: Now the plugin use wordpress style for tables
* Now you can download language file for wordpress directly from "Ceceppa Multilingua" page.

= 1.1.2 =
* Fixed duplicated items in "edit taxonomies form"

= 1.1.1 =
* Now you can choose the order of the flags :)

= 1.1.0 =
* Plugin works also custom post types :)
* Added "Language data" box for custom post type, now you can choose language, and link translations
* Added extra fields to custom taxonomies
* Now you can show flags also on custom post types
* Added flags for filter the list custom post types (doesn't appears with all plugins)

= 1.0.17 =
* Fixed fatal error on new install :(

= 1.0.14 =
* Now you can change language in "Quick edit" box
* Fixed minor bugs

= 1.0.13 =
* Fixed warning on tags

= 1.0.12 =
* Fixed home redirect with static page
* Fixed switch between categories
* Fixed Archives link

= 1.0.10 =
* Fixed Warning: implode()

= 1.0.8 =
* Fixed error "Wrong datatype for second argument"

= 1.0.7 =
* Fixed "not found" when try to preview post

= 1.0.6 =
* Fixed browser redirect
* Fixed language detection for categories

= 1.0.5 =
* Fixed issue with filter "Filter posts"
* Now you can choose the size of all flags :)

= 1.0.4 =
* Fixed layout in "Ceceppa Multilingua"
* Fixed minor bugs with category translation

= 1.0.3 =
* Fixed language detection

= 1.0.2 =
* Fixed issue with pages

= 1.0.1 =
* Fixed fatal error in "CML Widget" Recent posts

= 1.0.0 =
* Code optimization
* Fixed "Url Modification mode", now Pre-path and Pre-domain works correctly.
* If you choose pre-path mode add language slug also for category link istead of "?lang=##"
* Fixed "Translate the url for categories", now work correctly. This option is disabled by default, enable it on settings page.
* Fixed Catalan and Spanish flag
* Can show flag in your website withouth edit your template and without use widget. The options are available in "Settings" page

= 0.9.21 =
* Replaced hex2bin with UNHEX, now plugin is compatible also with Php < 5.4

= 0.9.20 =
* Now you can choose to translate link also for categories. The option is available in "Settings" page and support is experimental.

= 0.9.19 =
* Fixed filter in "All posts"

= 0.9.16 =
* Minor bug fixed

= 0.9.15 =
* Plugin is now compatible with Wordpress 3.5.2

= 0.9.14 =
* Fixed bug. Now you can show flags on bottom of the page

= 0.9.13 =
* Plugin now work correctly with php < 5.4

= 0.9.11 =
* Fixed sort in widget "CML: Recent Posts"

= 0.9.9 =
* Now you can use any symbol in "Widget titles" and "My translation page".
* Added documentation about shortcode in "Ceceppa Multilingua" -> "Shortcode"

= 0.9.4 =
* Fixed translation of widget titles.

= 0.9.1 =
* Fixed various bug

= 0.9.0 =
* You don't need to assign different menu to each language, because now all items of menu will be automatically translated.
* In the widget "CML: Language Chooser" add field "CSS ClassName"

= 0.8.7 =
* Fixed the translation of widget titles
* Added new widget: "CML: Text"

= 0.8.4 =
* Now you can translate also tag

= 0.8.1 =
* Fixed bug in "CML: Recent Posts"
* Added "Hide translations" in "Post" -> "All posts"

= 0.8.0 =
* Added "CML: Recent Posts" that show only recent posts of current language.
  In the widget "Categories", the categories will translated correctly

= 0.7.8 =
* Now page will be linked correctly

= 0.7.7 =
* Now translate post link correctly

= 0.7.5 =
* Now post link is translated correctly

= 0.7.4 =
* Fixed code

= 0.7.1 =
* Fixed translations

= 0.7.0 =
* Now you can translate a category in other languages, or use different categories for each language.
  Added Url Modification mode:
    Use Pre-Path Mode (Default, puts /%slug%/ in front of URL) (www.example.com/en/) (default)
    Use Pre-Domain Mode (en.example.com)
  It is enabled by default, you can change or disable in settings page

= 0.6.3 =
* If you use "static page" as homepage, the plugin add ?lang=[]&sp=1 to url

= 0.6.2 =
* Now "hide translations" work correctly

= 0.6 =
* Now you can hide translations of posts of the current language in the_loop()

= 0.5.4 =
* Menu will be changed correctly if you choose default permalink structure (?p=#)

= 0.5.3 =
* Fixed various bug.

= 0.5.1 =
* Set new language "enabled"

= 0.5.0 =
* Removed field "Main page" and "Main category" in "Languages settings" page.
  Assign language to each categories (it's not necessary for subcategories).

= 0.4.8 =
* Fixed msDropDown issue when 2 select has same id

= 0.4.6 =
* Autorefresh list "Link to the categories" withouth reload page.

= 0.4.4 =
* Fixed warnings

= 0.4.3 =
* Updates all files

= 0.4.1 =
* In edit post it's possible to see and switch to all linked posts, or you can add translation to it.

= 0.4 =
* Now you can have different menu for each language withouth edit your theme code.

= 0.3.7 =
* Fixed: Plugin doesn't work when table prefix wasn't "wp_"

= 0.3.6 =
* Fix error in options page.

= 0.3.5 =
* Get language info correctly during installation

= 0.3.4 =
* Fixed setlocale. Now locale will be changed correctly.
  Fixed linked categories. Now categories will be linked correctly, so filter post in homepage work correctly.
                           If you upgade from 0.3.3 or above, you must edit all linked categories by choosing
                           "Edit" from category page and save it.
= 0.3.3 =
* Fixed: setlocale. It was changed only in admin page

= 0.3.2 =
* Fixed same Notice in debug mode

= 0.3.1 =
* Added flags near title in "All posts" and "All pages
* Added checkbox for disable language

= 0.3 =
* Different post/page for each language
* Different menu for each language. (need to edit header.php)
* Translate widget's titles
* Group/Ungroup comments for this post/page/category that are available in each language
* Show notice when the post/page/category is available in the visitor's language
* Automatically redirects the browser depending on the user's language
* Widget for language chooser
* Filter some wordpress widgets, as "Least and Reads Posts", "Most read post", "Most commented"
* Filter search in according to current language
* Change wordpress locale according to current language, useful for localized themes
* Show the list flag of available languages on top or bottom of page/post
* Show list of all articles with their translatios, if exists
