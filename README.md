# 1t.ie
Quick Short link generator. This began as a 3-day coding challenge but here we are I guess ¯\_(ツ)_/¯

Live at [1t.ie](https://1t.ie)

### Features

1t.ie provides easy link shortening without needing to sign in. 
Shortened links are as short as possible and GET request parameters will be forwarded
to the resolved URL.

TL;DR*

- URL parameter forwarding
- Really short links
- No overhead; average 9ms URL resolution  
- No need to register
- Register to keep links permanent
- Anonymous links expire after 1 week

\*Not all features added currently  


### Installing on your server

First set up an SQL database and import `setup.sql` to build the table structure.
Once finished, modify the values in config.ini with your actual database login details.

Then, in a PHP / Apache web accessible directory run:
```bash
git clone https://github.com/xy3/1t.ie.git
cd 1t.ie
composer install
```

Done.

Todo
- My account page
- User accounts
- Link expiry handling
- Recent urls shortened by this PC / IP address
