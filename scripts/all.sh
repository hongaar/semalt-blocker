#!/bin/sh

echo "\nPULLING SPAMMY DOMAINS\n********************\n"
sh pull-domains.sh
echo "\nPULLING ROOT TLDS\n********************\n"
sh roottlds.sh
echo "\nCOMPACTING\n********************\n"
sh compact.sh
echo "\nEXPORTING\n********************\n"
sh export.sh
echo "\nRUNNING TIMER\n********************\n"
sh timer.sh
echo "\nRUNNING TESTS\n********************\n"
cd ..
phpunit
cd scripts
