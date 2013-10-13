#!/bin/bash

. /kunden/303805_65187/.bashrc

cd `dirname $0`/../../
app/console --env=prod bcrm:newsletter:confirm
app/console --env=prod bcrm:registration:confirm
app/console --env=prod bcrm:unregistration:confirm
app/console --env=prod bcrm:tickets:process-unregistrations
app/console --env=prod bcrm:tickets:create
app/console --env=prod bcrm:tickets:notify
app/console --env=prod swiftmailer:spool:send
