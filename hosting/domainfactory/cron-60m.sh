#!/bin/bash

. /kunden/303805_65187/.bashrc

cd `dirname $0`/../../
app/console --env=prod bcrm:mailchimp:update-participants-list 633fd5a389
