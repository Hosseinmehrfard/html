<?php

require_once 'mysql.php';

/**
 * Generate a UUIDv4 string.
 */
function uuidv4(): string
{
    $data = random_bytes(16);

    // Set version to 0100
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

try {
    $invitationCode = uuidv4();

    $insert = $connection->prepare(
        'INSERT INTO invitation_codes (invitation_code, used) VALUES (:code, 0)'
    );
    $insert->execute([':code' => $invitationCode]);

    echo "New invitation code created: {$invitationCode}\n";
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Failed to generate invitation code.';
}
