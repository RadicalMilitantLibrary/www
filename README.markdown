![ReadersAgainstDRM](img/logo.png "Readers Against DRM")

# The Radical Militant Library

This web interface is an attempt to raise the quality of ebooks.

## Other Resources

* [Manual](https://github.com/RadicalMilitantLibrary/manual/)
* [Template for ODT](/RadicalMilitantLibrary/www/blob/odt-template/reading_club.odt) ([copy from the onion-service](http://c3jemx2ube5v5zpg.onion/reading_club.odt))
* [Libre Office](https://www.libreoffice.org/download/) for editing the template
* [Public Wishlist](https://gitlab.com/lazy-book-crowd/more-bookz)
* [Database-Example](https://github.com/RadicalMilitantLibrary/database) 

## Requirements (as of news from 2017-01-08 by Jotunbane)

* HTTP Server (e.g. apache, nginx, ...)
* PHP
  + incl. GD library (for image manipulation)
  + enabled to use ZIP files (compiled with `--enable-zip`)
* PostgreSQL (or a shitload of work and some rewrite to enable another DBMS)
  + php_pgsql (or PHP compiled with `--with-pgsql'`)
  + the actual [database](https://github.com/RadicalMilitantLibrary/database) or a recent dump by Jotunbane (you may find contact via the [manual](https://github.com/RadicalMilitantLibrary/manual))

## Donation

Maybe you cannot code and have no time to engage.
The service is running as one of the oldest publicly known onion (back then called hidden) services.
It is maintained and mainly funded by Jotunbane, so if you are willing to help, send your bits:

via Bitcoin [19czh9hk8v7hMptokenBFZDNGX4aGiyTRN](bitcoin:19czh9hk8v7hMptokenBFZDNGX4aGiyTRN)

or Monero [49LJ7DffPBeZrBovTh2KzGZumU2N6m7PTQm12tfsKSMAMkAehMHaXoVZcdwmR7g7yNQ91QKopGL4BbGsHBZGTT8LR8kovxL](monero:49LJ7DffPBeZrBovTh2KzGZumU2N6m7PTQm12tfsKSMAMkAehMHaXoVZcdwmR7g7yNQ91QKopGL4BbGsHBZGTT8LR8kovxL)

## License Terms

Every contributor accepts that his code will be available to the public under the terms of at least one of the following licenses:

* [GNU GPLv2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
* [GNU GPLv3](https://www.gnu.org/licenses/gpl-3.0.en.html) or later
* [GNU AGPLv3](https://www.gnu.org/licenses/agpl-3.0.en.html) or later

The contributor can propose other licenses that do not guarantee fewer freedom.
