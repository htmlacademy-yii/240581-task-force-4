<?php

$config = parse_ini_file('/OpenServ/domains/config/taskforce_config.ini', true);

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host='.$config['mysql_host'].';dbname='.$config['mysql_db'],
    'username' => $config['mysql_un'],
    'password' => $config['mysql_pwd'],
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
