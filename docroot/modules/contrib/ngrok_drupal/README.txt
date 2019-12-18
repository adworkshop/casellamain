##What is ngrok ?

Ngrok allows developers to create secure tunnels to localhost or platform of
their choice. Main use cases of ngrok are:

- Demo without deploying
- Simplify mobile device testing
- Build webhook integrations with ease
- Run personal cloud services from your own private network

More info at https://ngrok.com

## What's wrong when using ngrok with drupal ?

If your setup uses VirtualHost for development, you need to rewrite headers to
point ngrok to the site you want to expose.

    ngrok http -host-header=rewrite my.domain.local:80

This leads to blocking problems when trying to initiate sessions (cookies
sessions can't be set due domain mismatch) or exposing absolute URLs that still
points to local domain instead of external ngrok.io one.

## What's the purpose of this module ?

This module alters the behaviour of Drupal core to resolve the issues described
above. When a ngrok session is initiated, it:

- Redefine the cookie_domain to your subdomain.ngrok.io
- Alter external URLs to use the subdomain.ngrok.io as base URL

## What if I don't patch the core files ?

You still get a lot of functionality. However, all redirsect responses will
fail with error like  `Redirects to external URLs are not allowed by default`.
Manually entering the redirected url will work.
