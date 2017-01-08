![ReadersAgainstDRM](img/logo.png "Readers Against DRM")

# The Radical Militant Library

This web interface is an attempt to raise the quality of ebooks.

## Other Resources

* [Manual](https://github.com/RadicalMilitantLibrary/manual/)
* [Template for ODT](http://c3jemx2ube5v5zpg.onion/reading_club.odt) (links the onion-service)
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

via Bitcoin [1MjAY5FZ9To6M1VHvgWa95WzsVtD3X9NaA](bitcoin:1MjAY5FZ9To6M1VHvgWa95WzsVtD3X9NaA)

## License Terms

Every contributor accepts that his code will be available to the public under the terms of at least one of the following licenses:

* [GNU GPLv2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
* [GNU GPLv3](https://www.gnu.org/licenses/gpl-3.0.en.html) or later
* [GNU AGPLv3](https://www.gnu.org/licenses/agpl-3.0.en.html) or later

The contributor can propose other licenses that do not guarantee fewer freedom.
