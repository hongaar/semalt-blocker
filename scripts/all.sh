#!/bin/sh

printf "\nPULLING SPAMMY DOMAINS\n********************\n"
sh pull-domains.sh
printf "\nPULLING ROOT TLDS\n********************\n"
sh roottlds.sh
printf "\nCOMPACTING\n********************\n"
sh compact.sh
printf "\nEXPORTING\n********************\n"
sh export.sh
printf "\nRUNNING TIMER\n********************\n"
sh timer.sh
printf "\nRUNNING TESTS\n********************\n"
cd ..
phpunit
cd scripts
