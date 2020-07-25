# 1t.ie
Quick link shortener


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

In a PHP/Apache web accessible directory run:
```bash
git clone https://github.com/xy3/1t.ie.git
cd 1t.ie
composer install
```

Todo
- User interface
- User accounts
- Link expiry handling
