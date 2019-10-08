=== Authorize IP Address ===
Contributors: jovevskitoni
Tags: authentication, whitelisting, authorize ip, security, login, two factor, brute force attacks, lockdown, password
Requires at least: 3.0.1
Tested up to: 4.7.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Authorize IP Address prevent login from unknown IP address.  Whitelist User IP addresses. If a user logs in from an unknown IP the plugin sends an email to the user and optionally the admin with a one-time key.

== Description ==

Authorize IP Address provides enhanced security by requiring users to whitelist their IP address.
If the IP address is not recognized, the plugin will send an email to the user with a link that contains a one-time key. Optionally the blog administrator can also be notified.

If a user logs in from a known IP address no further action is required.

**What does this Plugin do?**

1. Prevent from sharing login details. Users can login from only approved ip address from the user via email.
1. Each time a user logs in, the plugin will compare their existing IP address to the last seen IP address.
1. User First Time login IP address will be automaticly add to whitelist.
1. If the IP does not match or no IP addresses have been whitelisted, an email will be sent to the users registered email address.
1. The user must login to their email and click the included link, which contains the one-time password.
1. The plugin can be configured to also send an email to the blog administrator as well as the user.  


== Installation ==

This Plugin works without you having to make any changes. 

1. Search for the plugin using the WordPress Plugin Installer OR download and unzip the directory into your plugins directory.
1. Activate the Plugin through the 'Plugins' menu in WordPress - Upon activation, your current IP will be automatically whitelisted.
1. Optionally enable notifications of Blog Admin.
1. Enjoy the enhanced security!

== Frequently Asked Questions ==


= Way I develop this Plugin? =

I develop this plugin to prevent from sharing login details. It is specialy Good for Membership websites to prevent from selling memberships for free.
Only Original user can login with his account because everytime when somebody will try to login with his login and password from unknown ip he will be blocked and asked for email confirmation.


= Can I help you develop this Plugin? =

Yes, I am open to anyone with experience who can provide assistance in making this Plugin better.  Just send me a message.

= How to ask a question? =

Email me at jovevskitoni@gmail.com and ask me a question.

== Screenshots ==

1. The admin panel of the plugin showing the default values.

== Changelog ==


= 1.0.1 = 
* Added Manual approve and remove IP Address for only one client 
= 1.0.0 = 
* Fixed: Works For all types of users


