=== Wild Apricot Login ===
Contributors: Wild Apricot
Donate link: http://www.wildapricot.com/
Tags: Wild Apricot, members, membership management, events, event management, single sign-on
Requires at least: 4.0.1
Tested up to: 6.6
Stable tag: 1.0.16
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides single sign-on service for Wild Apricot members to provide access to restricted Wild Apricot content.

== Description ==

The [Wild Apricot](http://www.wildapricot.com/) Login plugin allows you to restrict content on your WordPress to your Wild Apricot members, and provide access to restricted Wild Apricot content such as member directories and member-only events. Any restricted Wild Apricot content - embedded using Wild Apricot widgets - can be accessed without further authentication. 

You can display a login button for single sign-on by adding a widget - installed along with the Wild Apricot Login plugin - to the header in your WordPress theme layout, or by inserting a shortcode in your page content. A shortcode can be added to a WordPress page to restrict WordPress content to Wild Apricot members. As well, you can use the plugin to add Wild Apricot membership levels as WordPress roles.

== Installation ==

There are two ways to install the Wild Apricot Login plugin: automatically and manually.

To install the plugin automatically, follow these steps: 

1. Log in to your WordPress site.
1. Within your Dashboard, hover over the Plugins menu and choose the Add New option.
1. Search for the Wild Apricot Login plugin.
1. Once you've found the Wild Apricot Login plugin, click the Install Now button. 
1. When prompted to confirm your choice to install the plugin, click OK.
1. After the plugin is installed, click the Activate Plugin link.
1. Within your Dashboard, hover over the Settings menu and choose the Wild Apricot Login option.
1. Within the Wild Apricot Login Settings, enter your Wild Apricot API key, Client ID, and Client secret values. You can obtain these values when you authorize WordPress from the Authorize applications screen in Wild Apricot.
1. Optionally, you can change the default login button label.
1. Click the Save Changes button. The API key, Client ID, and Client secret fields should now appear as set.
1. Once those fields appear as set, you can click the Update button to update your WordPress roles with your Wild Apricot membership levels. No existing roles will be removed from your WordPress account.

To install the plugin manually, follow these steps:

1. Click the Download link above to download the zip file containing the Wild Apricot plugin files.
1. Within your Dashboard, hover over the Plugins menu and choose the Add New option.
1. Click the Upload Plugin button. 
1. Click the Choose File button and locate the zip file you downloaded.
1. Click the Install Now button.
1. After the plugin is installed, click the Activate Plugin link.
1. Within your Dashboard, hover over the Settings menu and choose the Wild Apricot Login option.
1. Within the Wild Apricot Login Settings, enter your Wild Apricot API key, Client ID, and Client secret values. You can obtain these values when you authorize WordPress from the Authorize applications screen in Wild Apricot.
1. Optionally, you can change the default login button label.
1. Click the Save Changes button. The API key, Client ID, and Client secret fields should now appear as set.
1. Once those fields appear as set, you can click the Update button to update your WordPress roles with your Wild Apricot membership levels. No existing roles will be removed from your WordPress account.

== Frequently Asked Questions ==

= How do I add a button for single sign-on to my WordPress site? =

You can display a login button for single sign-on by adding a widget - installed along with the Wild Apricot Login plugin - to the header in your WordPress theme layout, or by inserting a shortcode in your page content.

To display the login button within your WordPress theme header - which can appear as a sidebar for some themes - follow these steps:

1. Under your Dashboard, hover over Appearance then select the Themes option.
1. From the Themes screen, click the Customize button for your current theme.
1. Click the > button to the right of Widgets.
1. Within the Widget area, click the Add a widget button.
1. Search for the Wild Apricot Login widget.
1. Within the settings for the Wild Apricot Login widget, you can change the Login button label and specify the name of the WordPress page you want to redirect members to after logging in. Leave the Redirect page field blank if you want them to remain the current page.

To display the login button for single sign-on on a WordPress page, add the following shortcode to a page in either visual or HTML mode:

[wa_login login_label="Login" logout_label='Logout' redirect_page="/"]

where the following attributes are optional:
login_label - The label appearing on the login button
logout_label - The label appearing on the logout button
redirect_page - The WordPress to redirect the member to after logging in

= How do I restrict WordPress content to Wild Apricot members? =

To restrict WordPress content to Wild Apricot members, add the following shortcode to a page or custom menu:

[wa_restricted roles="Gold, Silver" message="Log on to view restricted content."]
Restricted content.
[/wa_restricted]

where the following attributes are optional:
roles - WordPress roles corresponding to Wild Apricot membership levels to which you want the content restricted
message - Message appear in place of the restricted content. A login button will appear below the message

The content between the open and close tags will only appear to authorized users. You can include widget code to display restricted Wild Apricot content.

You can add a not: operator at the start of the roles attribute to specify all roles other than those specified. And use 'all' to specify all roles.

[wa_restricted roles="not:Bronze" message="Log on to view restricted content."]
Restricted content.
[/wa_restricted]

== Screenshots ==

1. Settings for the Wild Apricot Login plugin.
2. Restricted Wild Apricot content on WordPress page.

== Changelog ==
= 1.0.16 =
* Fixed login for WA widgets to work without 3rd party cookies

= 1.0.15 =
* Fixed login for WA widgets

= 1.0.14 =
* Changed settings validation

= 1.0.13 =
* Fixed content restriction for wa members

= 1.0.12 =
* Fixed WA API key saving after WA oAuth update

= 1.0.11 =
* Fixed login for re-created WA users

= 1.0.10 =
* Removed an issue related with wysiwyg-editor crush

= 1.0.9 =
* Added buttons to editor for shortcodes, wa_login and wa_restricted
* Enabled nested shortcodes for wa_restricted shortcode
* Enabled "all" value for roles in wa_restricted shortcode
* Enabled html-markup in wa_restricted shortcode message

= 1.0.8 =
* Enabled api-request debugging for admin

= 1.0.7 =
* Removed an issue with pantheon.io

= 1.0.6 =
* Removed a conflict with Beaver Builder

= 1.0.5 =
* Removed a conflict with Wordfence Security
* Removed a conflict with WP-SpamFree

= 1.0.4 =
* Removed a conflict with WPFront User Role Editor

= 1.0.3 =
* Removed an issue with retaining plugin settings after its upgrade

= 1.0.2 =
* Removed a bug related to inability of logging in if WP was installed to non-root folder

= 1.0.1 =
* Initial stable release.

== Upgrade Notice ==

= 1.0.16 =
* Fixed login for WA widgets to work without 3rd party cookies

= 1.0.15 =
* Fixed login for WA widgets

= 1.0.14 =
* Changed settings validation

= 1.0.13 =
* Fixed content restriction for wa members

= 1.0.12 =
* Fixed WA API key saving after WA oAuth update

= 1.0.11 =
* Fixed login for re-created WA users

= 1.0.10 =
* Removed an issue related with wysiwyg-editor crush

= 1.0.9 =
* Added buttons to editor for shortcodes, wa_login and wa_restricted
* Enabled nested shortcodes for wa_restricted shortcode
* Enabled "all" value for roles in wa_restricted shortcode
* Enabled html-markup in wa_restricted shortcode message

= 1.0.8 =
* Enabled api-request debugging for admin

= 1.0.7 =
* Removed an issue with pantheon.io

= 1.0.6 =
* Removed a conflict with Beaver Builder

= 1.0.5 =
* Removed a conflict with Wordfence Security
* Removed a conflict with WP-SpamFree

= 1.0.4 =
* Removed a conflict with WPFront User Role Editor

= 1.0.3 =
* Removed an issue with retaining plugin settings after its upgrade

= 1.0.2 =
* Removed a bug related to inability of logging in if WP was installed to non-root folder

= 1.0.1 =
* Initial stable release.