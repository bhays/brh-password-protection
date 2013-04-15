Koken Password Protection
=========================

Plugin for [Koken](http://koken.me) to allow password protection on a per URL basis.

This is by no means secure, but it does restrict access to content. Depending on your theme, there may be many ways to get to the same content. In this event, you will need to define multiple URLs or change your theme setup.

Filtered output uses [PHP Simple HTML DOM Parser](http://simplehtmldom.sourceforge.net/) by S.C. Chen.


Requirements
------------

1. [Koken](http://koken.me) installation

Installation
------------

1. Upload the brh-password-protection folder to your Koken installation's storage/plugins directory.

2. Sign in to Koken, then visit the Settings > Plugins page to activate the plugin.

3. Once activated, click the Setup button and then add the URL and passwords of your choice.

4. Add your email address to display at the end of the password form for people to contact you for access.

Usage Notes
------------

- URL and password separated by `|`

- Each URL`|`Password should be separated by `,` 

- The URL should not include the domain, and should only be the end of the URL (See examples below)

- A URL can only be defined once

- Only one password is saved at a time, so if you would like multiple URLs protected with only one login, use the same password

- Passwords cannot contain the `|` or `,` characters included, or things will break.


Examples
------------

Protect the page `http://koken.me/gallery` with password `letmein`

    gallery|letmein
    
Now also protect the page `http://koken.me/essays/22` with password `supersecret`

    gallery|letmein,essays/22|supersecret


