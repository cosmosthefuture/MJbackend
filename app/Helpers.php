<?php

use App\Services\SMS\SMSService;

if (!function_exists('sendSMS')) {
    function sendSMS($phone_number, $message, $gateway = 'smspoh')
    {
        $smsService = new SMSService($gateway);
        return $smsService->sendNotification($phone_number, $message);
    }
}

if (!function_exists('getSMSBalance')) {
    function getSMSBalance($gateway = 'smspoh')
    {
        $smsService = new SMSService($gateway);
        return $smsService->getAccountBalance();
    }
}

if (!function_exists('ext2mime')) {
    function ext2mime($extension)
    {
        $mimeType = config("mime.mimeTypes.$extension");
        return $mimeType;
    }
}

if (!function_exists('mime2ext')) {
    function mime2ext($mimeType)
    {
        $extension = config("mime.extensions.$mimeType");
        return $extension;
    }
}

if (!function_exists('isBase64')) {
    function isBase64(string $string): bool
    {
        if (empty($string) || !is_string($string)) {
            return false;
        }
        if (strlen($string) % 4 !== 0) {
            return false;
        }
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) {
            return false;
        }
        return true;
    }
}

if (!function_exists('isDataUrlBase64')) {
    function isDataUrlBase64(string $string): bool
    {
        return preg_match('/^data:[a-zA-Z0-9\/\+\-\.]+;base64,/', $string) === 1;
    }
}

if (!function_exists('trimDataUrlPrefix')) {
    function trimDataUrlPrefix(string $dataUrl): string
    {
        return preg_replace('/^data:[a-zA-Z0-9\/\+\-\.]+;base64,/', '', $dataUrl);
    }
}

if (!function_exists('getMimeType')) {
    function getMimeType(string $content): string
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($content);
    }
}

if (!function_exists('isBinary')) {
    function isBinary(string $content): bool
    {
        $mimeType = getMimeType($content);
        $binaryMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/webp',
            'image/svg+xml',
        ];
        return in_array($mimeType, $binaryMimeTypes);
    }
}

if (!function_exists('hasFileExtension')) {
    function hasFileExtension(string $filename): bool
    {
        $fileInfo = pathinfo($filename);
        return isset($fileInfo['extension']) && !empty($fileInfo['extension']);
    }
}

if (!function_exists('getFileExtension')) {
    function getFileExtension(string $filename): string
    {
        $extension = '';
        $fileInfo = pathinfo($filename);
        if (isset($fileInfo['extension']) && !empty($fileInfo['extension'])) {
            $extension = $fileInfo['extension'];
        }

        return $extension;
    }
}

if (!function_exists('generateRandomAlphanumeric')) {
    function generateRandomAlphanumeric($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}

if (!function_exists('formatString')) {
    function formatString($string)
    {
        // Split the string by underscores
        $words = explode('_', $string);

        // Capitalize the first letter of each word
        $words = array_map('ucfirst', $words);

        // Join the words with spaces
        $formattedString = implode(' ', $words);

        return $formattedString;
    }
}

if (!function_exists('uni2zg')) {

    function uni2zg($text)
    {
        return Rabbit::uni2zg($text);
    }
}

if (!function_exists('generateSimpleUuidV4')) {
    function generateSimpleUuidV4()
    {
        // Generate 16 bytes (128 bits) of random data
        $data = random_bytes(16);

        // Set the version to 0100 (UUID v4)
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set the variant to 10xx
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36-character UUID string
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}


if (!function_exists('generateUniqueFilename')) {
    function generateUniqueFilename($extension = '')
    {
        $uuid = generateSimpleUuidV4();
        if ($extension) {
            // Ensure the extension does not contain any invalid characters
            $extension = preg_replace('/[^a-zA-Z0-9]/', '', $extension);
            return $uuid . '.' . $extension;
        }
        return $uuid;
    }
}

if (!function_exists('generateUniqueFilenameWithTimestamp')) {
    function generateUniqueFilenameWithTimestamp($extension = '')
    {
        $uuid = generateSimpleUuidV4();
        $timestamp = time(); // Current Unix timestamp
        if ($extension) {
            $extension = preg_replace('/[^a-zA-Z0-9]/', '', $extension);
            return $timestamp . '_' . $uuid . '.' . $extension;
        }
        return $timestamp . '_' . $uuid;
    }
}
