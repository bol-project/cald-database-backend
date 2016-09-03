#!/bin/bash

usage="Usage:
    source_db={source_db_name} target_db={target_db_name} user={db_user_name} ./migrate.sh"

dir="$(dirname "$0")"

: "${source_db:?Need to set source_db: $usage}"
: "${target_db:?Need to set target_db: $usage}"
: "${user:?Need to set user variable (user has to have read access to $source_db and write access to $target_db): $usage}"

(cat $dir/migrate.sql | sed s/\:new_schema_name\:/$target_db/g) > $dir/specific_migrate.sql
mysql -D $source_db -u $user -p < $dir/specific_migrate.sql && echo "migration done"
rm $dir/specific_migrate.sql