<?php

if (!isset($argv[1])) {
    echo 'Usage: php convert.php <psono-json-file.json>' . PHP_EOL;
    exit;
}

/**
 * Recursively get all items into a single array.
 *
 * @param array $data - parent data
 * @param array $items - List of items
 *
 * @return array
 */
function recursivelyGetItems(array $data, array &$items = []): array
{
    if (array_key_exists('items', $data)) {
        $items = array_merge($items, $data['items']);
    }
    
    // If sub folder.. call again!
    if (array_key_exists('folders', $data)) {
        foreach ($data['folders'] as $folder) {
            if (array_key_exists('items', $folder)) {
                foreach ($folder['items'] as $subItem) {
                    $items[] = array_merge(['folder' => $folder['name']], $subItem);
                }
            }
        }
    }
    return $items;
}

// Open source JSON and decode into array
$rawData = file_get_contents($argv[1]);
$data = json_decode($rawData, true);
unset($rawData);
if (!is_array($data)) {
    echo 'Error decoding JSON.' . PHP_EOL;
    exit;
}

$bitwardenHeaders = [
    'folder',
    'favorite',
    'type',
    'name',
    'notes',
    'fields',
    'login_uri',
    'login_username',
    'login_password',
    'login_totp'
];

// Open new file for writing
$fp = fopen('export.csv', 'w');
fputcsv($fp, $bitwardenHeaders);

// Get items from nested JSON array
$items = recursivelyGetItems($data);

foreach ($items as $item) {
    $mapper = [
        $item['folder'] ?? '',
        '',
        'login',
        $item['name'],
        ($item['note_notes'] ?? ($item['website_password_notes'] ?? '')),
        '',
        $item['website_password_url'] ?? '',
        $item['website_password_username'] ?? '',
        $item['website_password_password'] ?? '',
        ''
    ];

    fputcsv($fp, $mapper);
}

fclose($fp);
