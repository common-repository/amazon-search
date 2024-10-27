=== Amazon Search ===
Contributors: asmaloney
Donate link: http://imol.gotdns.com/
Tags: Amazon, Amazon Associate, Amazon links, Amazon shortcode, books, search, plugin, widget
Requires at least: 2.2
Tested up to: 2.7.1
Stable tag: 1.2.0

Lets you add links to Amazon using a special markup.
Also includes an optional widget to search Amazon and display results in your blog.

== Description ==

This plugin allows you to link directly to items in your
posts using a special tag of the form:

**[amazon ASIN=0123456789]Fancy Title[/amazon]**

The plugin lets you set associate IDs for each international amazon server and set one server
as the default for your site.

It also includes an Amazon Search widget which will search any of the amazon servers
and produce search results with direct links to products.

It also provides an XSLT file for the amazon server to access, which processes it together with the XML results of
the search, and then returns HTML.  This saves your server from having to process the XML itself.

It uses css to format the search widget and the search results.  It will look in its css dir for theme-specific css files
too, so if you have one in there called **your-theme-name.css**, it will include it after **default.css**.  This way you may
customize search results for a given theme by dropping in a new css file.  So if you allow your users to switch themes, you can include
a css file for each one and the search will look correct when it is switched.

= Version History =

* v1.2.0 [23 March 2009]
	* [fix] fixes for potential installation problems on WordPress 2.7
	* [new] add buttons to the TinyMCE and QuickTag editors to make the Amazon markup easier [code from <a href="http://sillybean.net" target="_blank">Stephanie Leary</a> - thanks Stephanie!]
	* [new] new editor buttons are optional [They are on by default but may be turned off in the preferences.]
	* [new] made the search part of the plugin optional [If you just want to use the text links, search may be completely disabled in the preferences.]
	* [other] code reorganization and cleanup

* v1.1.3 [20 July 2008]
	* [fix] fix dumbness introduced in 1.1.2

* v1.1.2 [20 July 2008]
	* [new] code refactoring of the ipNation stuff for some future work
	* [new] check if the ip2Nation database is installed when displaying options on the admin page

* v1.1.1 [23 May 2008]
	* [fix] fix for PHP 4.x
	* [fix] better integration with Silver Light theme

* v1.1.0 [26 Apr 2008]
	* [new] added ability to specify a text link in posts by using a special tag: [amazon asin=0123456789]link text[/amazon]
	* [new] new text links will use <a href="http://www.ip2nation.com/ip2nation" target="_blank">ip2Nation database</a> to do simple geo-location

* v1.0.2 [02 Apr 2008]
	* [fix] fix default XSL path

* v1.0.1 [22 Mar 2008]
	* [new] add CSS for Silver Light theme
	* [fix] fix incorrect paths due to last-minute name change

* v1.0.0 [08 Jan 2008]
	* initial version

== Installation ==

1. Upload the whole amazon-search directory to your /wp-content/plugins/ directory [it must be called amazon-search]
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure it using the **Settings -> Amazon Search** page in WordPress
1. Go to the **Appearance -> Widgets** page and drag the **Amazon Search** widget into a sidebar.

== Frequently Asked Questions ==

= Why do I just get a bunch of text when I search? =

Check that **XSL file** is set properly in your prefs and is a full URL to an externally visible file.  The Amazon servers will grab
this file to format your results.

Note that if you change the XSL file it may take a minute or two for the Amazon server's cache to update it.

= What did you use for development? =

* WordPress 2.7.1
* MAMP 1.7.1
* Firefox 3.1b3
* MacOS X 10.5.6

= What are some of the limitations? =

* you cannot choose the sorting order
* you cannot choose which item data that is shown in the result [unless you know XSLT]
* the search part of the plugin requires that your HTTP server have cURL support

= Uh... Why? =

I originally wrote this for the Geeklog CMS.  When I wrote it, Amazon did not provide widgets,
and Amazon Associates received more income for direct links than for others.  So I wrote this so Associates could
generate more direct-link income from their sites.

Amazon now provides a widget that does most of what the search provides.  What it doesn't do is integrate nicely with your website.  This plugin
is intended to provide seamless integration with your site's theme and give you more control over the layout and information presented.

After porting the search facility I added support for the special tags to add text links to posts. <a href="http://sillybean.net" target="_blank">Stephanie Leary</a> then
provided me with some code to add buttons to the editors which I modified and included with the plugin.

If I get decent feedback and people are using it, I will continue to improve it.

= How do I contact you? =

You can email my gmail account **imol00**.

== Screenshots ==

1. An example search using the classic theme.
1. The admin screen
1. The Amazon link button in the editor

==Configuring The Plugin==

In the Admin section, under **Settings -> Amazon Search** you may configure the following:

= Associate IDs =

In this section, fill in any associate IDs you have.  You may also select one of the servers to be the
default for your users.  If you live in France, you might choose the amazon.fr server, for example.
Any IDs left blank will be filled in with mine.

= Development Contribution =

Ah, here's the hook!  I've set it up so that every 'N' searches [or page displays if you are using the text links],
the links which are produced use my
associate IDs.  You can choose a value for 'N' here.  No need to go hacking if you don't want to support
development - just set it to 0 and it won't ever substitute my associate IDs.

= Text Links =

If **Use Text Links** is on, the plugin will process your posts looking for a special markup and convert them to links.

To add a text link to an Amazon product, simply use the following format:

  [amazon ASIN=0743279794]Fancy Title[/amazon]
  
ISBN may be used in place of ASIN - they are effectively the same thing.  It is a case-insensitive match, so 'asin' is the same as 'ASIN'.

This tag will be converted to a link of the form:

&lt;a href="http://www.amazon.com/dp/0743279794/?tag=yourTag-20"&gt;Fancy Title&lt;/a&gt;

If you have **Use ip2Nation** checked and the <a href="http://www.ip2nation.com/ip2nation" target="_blank">ip2Nation database</a> is installed, it may be used to locate the best Amazon site for the visitor.  Any country not handled specially will
use your default Amazon server.  If you have the **Development Contribution** set in your prefs, a page load will count as a 'search'.

**NOTE:** The handling of which Amazon site is best per country is incomplete at best.  I would appreciate any feedback or additions/corrections
to the list [which is found in the getAmazonServerFromIP() function in amz-search.php].

**Add Editor Buttons** allows you to turn off the additional buttons in the editors which provide a quick way to enter an amazon link.

= Searching =

**Use Searching** allows you to turn off the search capability completely in case you just want to use the text links.

**Default Category** is the one that will be used when the user searches from the widget.  When they
get the results, they will be able to change the category on the search page.

**Default Search Term** is what is used if the user doesn't enter a search term.  Right now it defaults
to 'WordPress'.  You will probably want to change it to something appropriate to your site.

You may specify an XSLT file that will be sent to the amazon servers for processing.  **This file must be publicly
accessible** because the amazon servers will be reading it to provide formatting. 
If you don't know what this means, you better leave it alone.  The XSLT file may be edited if you want to
customise the results produced by the search.  I am certainly *not* an XSLT expert, so if you have any suggestions,
please contact me.

You may also set the size of the images that are to be returned in the search or 'None' to turn them off.
Watch out if you set this to 'Large'!
