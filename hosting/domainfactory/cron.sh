#!/bin/bash

. /kunden/303805_65187/.bashrc

cd `dirname $0`/../../
app/console --env=prod bcrm:newsletter:confirm
app/console --env=prod swiftmailer:spool:send
