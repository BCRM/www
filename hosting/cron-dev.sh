#!/bin/bash

app/console bcrm:payments:process --verbose
app/console bcrm:registration:pay --verbose
app/console bcrm:unregistration:confirm --verbose
app/console bcrm:tickets:process-unregistrations --verbose
app/console bcrm:tickets:create --verbose
app/console bcrm:tickets:send --verbose
app/console swiftmailer:spool:send
