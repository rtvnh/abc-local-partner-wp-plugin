# ABC Manager - Local Partner - Wordpress Plugin

[![Author](http://img.shields.io/badge/author-@angrybytes-blue.svg?style=flat-square)](https://twitter.com/angrybytes)
[![Software License](https://img.shields.io/badge/license-proprietary-brightgreen.svg?style=flat-square)](LICENSE.md)

Wordpress Plugin to post new updates to ABC Manager of NH/AT5

## Setup

When first setting up the plugin by installing it through WordPress plugin menu, select *Add New*. After that you get
the dashboard where all the different plugins showed and are publicly accessible WordPress itself, in the header you see
Add Plugins and next to that stands *Upload Plugin*. After clicking that a section pops up, where you can select a file
to upload, after downloading this repository you can add that plugin by selecting the downloaded Zip file.

If the plugin is being installed, you can find it under Settings > ABC Manager, in here you find a few options:

- ABC Manager URL API
- Partner Token

### ABC Manager URL API

For your WordPress environment to communicate between ABC Manager and WordPress it needs a connection between the two
environments through a URL API, this URL will be provided by RTV NH.

### Partner Token

To validate the communication we use a Partner Token which is provided by oAuth, this simply verifies if it's the
correct application which communicate with ABC Manager. It's a simple and safe option to communicate. The Partner Token
is unique and should not be shared with any other but yourself. Otherwise, we can't register which application is sending
news articles to ABC Manager, so it's safe but also very necessary.

If you filled in the credentials, don't forget to click the *Save Changes* button
