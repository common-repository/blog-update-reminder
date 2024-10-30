=== Plugin Name ===
Contributors: samgruskin
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=H8WQ2JLE6GJSQ
Tags: admin, administration, automatic, blog, code, custom, email, mail, manage, notification, plugin, plugins, post, posts, user, users, remind, reminder, daily, weekly, monthly, update, frequency, interval, days, time, timeframe, number, author, notify, commitment, regular, individual, website, message
Requires at least: 2.9.2
Tested up to: 3.2.1
Stable tag: 2.0.3

Track one or multiple authors' blog posting frequency and email-notifies these authors if they haven't posted within a set timeframe.

== Description ==

Plugin homepage: http://samgruskin.com/programming/blog-update-reminder/

Blog Update Reminder is designed to remind a Wordpress blog's author(s) with an email if they haven't made a blog post in a set number of days. When this plugin is installed, it automatically finds all authors (users) of the blog, and sets their "interval of days" to zero. The list of authors in Blog Update Reminder's settings will automatically update with the addition or removal of Wordpress blog users. The list of users and their set "interval of days" can be viewed from Blog Update Reminder's settings panel, found under the Wordpress "Settings" menu.

The setting of zero ("0") means that Blog Update Reminder will be disabled for that user, and that user will not receive any reminder emails. Each user's interval of days can be set and customized individually so that it will remind an author when a blog post hasn't been made in a set amount of time. The plugin will email the user once the amount of time since the last blog post has surpassed the set "interval of days". No emails will be sent while the number of days since the last post remains lower than the set "interval of days". So, in order for a user to stop receiving email reminders, the user will need to make a blog post or update his/her Blog Update Reminder settings.

By default, this plugin will send email reminders to its users on a daily basis after the interval of days since a blog post has passed. The frequency of the email reminders can be modified on the settings page found in a drop-down menu. 

This plugin uses Wordpress' built-in functionality for cron-jobs. This means that in order for this plugin to function properly and send reminder emails to its users, the blog's website must be accessed by anyone at least once per day. Otherwise, the users will receive their reminder emails the next time someone visits the blog website.

Due to this plugin's simple and small data structure, all settings are saved in a file called 'blogreminder.data' in the same directory as 'blogreminder.php'. Do not delete this file, otherwise Blog Update Reminder will return user's settings to the default interval of days, zero ("0").  If there are errors creating or writing to the file, check permissions on the `/wp-content/plugins/blog-update-reminder/` directory.

== Installation ==

1. Upload the folder `blog-update-reminder` to the `/wp-content/plugins/` directory.
2. Activate this plugin through the 'Plugins' menu in WordPress.
3. Customize this plugin through the 'Settings' -> 'Blog Update Reminder' menu in Wordpress.

== Frequently Asked Questions ==

= How come I did not receive an email reminder? =

Please make sure that your server is capable of sending mail using the PHP mail function.

In some cases, reminder messages are put in the spam folder. Check your filter settings, and train your email client that these messages are not spam.

= How can I change the reminder email's text? =

Unfortunately, right now I do not have an elegant solution for this. This feature will (hopefully) be available in a future version.

If you feel comfortable with modifying PHP code, look at the source for `blogreminder.php`. The function `send_reminder` handles the subject and message composition, and then sends the email. The email message is built starting at line 108.

== Screenshots ==

1. Blog Update Reminder settings panel.

== Changelog ==

= 2.0.3 =
* Added functionality to change email reminder frequency to time intervals other than 'daily'

= 1.0.1 =
* Directory name fixes

= 1.0 =
* Initial version!

== Upgrade Notice ==

= 2.0.3 =
* Added functionality to change email reminder frequency to time intervals other than 'daily'

= 1.0 =
* Initial version!

== Background and Contact ==

*A little background story...*

I designed this Wordpress plugin for a company I worked for, Carma Systems, Inc. (http://www.carmasys.com/). We have an internal company blog to keep our different teams in touch with eachother, and up to date on the work we do. It is important to everyone at the company to keep the blog updated, but it is easy to forget about it when everyone is busy! That's how Blog Update Reminder was born... the internal blog has many authors, and each author has different requirements for how often they should post, if at all. The system works, because blog authors are sent an email daily (or any specified frequency) until a blog post is made (it's kind of like, the plugin won't stop annoying you until you do what you're supposed to do!). Everyone enjoys the plugin, because authors are happy that they won't forget about making a post, and our internal blog readers benefit from staying up to date with the latest company news.

I feel that my plugin can work as a solution for many types of blogs. An individual may have a commitment to regularly make posts on his/her personal blog, or a company/group may have several people that need to make posts but some people may need to post more frequently than others. All in all, this plugin can keep a blog from dying, which happens too often (unfortunately) in the real world.

This is my first Wordpress plugin, and I had a lot of fun developing it! I hope other people will find it useful too. I appreciate any feedback (such as additional features and bugs), feel free to email me at sam@samgruskin.com. Thanks and enjoy! :-)

http://samgruskin.com/programming/blog-update-reminder/
