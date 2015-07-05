# BarCamp RheinMain – Website

[![Build Status](https://travis-ci.org/BCRM/www.png)](https://travis-ci.org/BCRM/www) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/BCRM/www/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/BCRM/www/?branch=master)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/40ee3236-6312-42b7-a1e9-1d54b249ff34/big.png)](https://insight.sensiolabs.com/projects/40ee3236-6312-42b7-a1e9-1d54b249ff34)

This is the source code for [barcamp-rheinmain.de](http://barcamp-rheinmain.de/).

Design: [Martin Kraft](http://martinkraft.com/)  
Webdesign: [Alex Wenz](http://alexwenz.de/)  
Development: [Markus Tacker](https://cto.hiv/)

## Setup

This is a Symfony2-Project. See [their extensive documentation](http://symfony.com/doc/2.3/book/installation.html) on how to get this running.

In a nutshell:

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install
    # Fix permissions
    APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd' | grep -v root | head -1 | cut -d\  -f1`
    sudo setfacl -R -m u:$APACHEUSER:rwX -m u:`whoami`:rwX app/cache app/logs
    sudo setfacl -dR -m u:$APACHEUSER:rwX -m u:`whoami`:rwX app/cache app/logs
    # Init database
    app/console doctrine:schema:create
    app/console doctrine:fixtures:load --append


## LICENSE

Copyright (c) 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of
the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
