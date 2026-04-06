<?php
$json = file_get_contents('https://raw.githubusercontent.com/IderDelgado/provincias-cantones-parroquias-del-ecuador/master/ecuador.json');
$data = json_decode($json, true);
$firstProvince = array_key_first($data);
echo "Primer elemento: " . $firstProvince . "\n";
print_r(array_keys($data[$firstProvince]));
if (isset($data[$firstProvince]['cantones'])) {
    $firstCanton = array_key_first($data[$firstProvince]['cantones']);
    echo "Cantones keys:\n";
    print_r(array_keys($data[$firstProvince]['cantones'][$firstCanton]));
}
