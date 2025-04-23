<?php
/**
 * PCC Hotel - Helper Functions
 */

/**
 * Format a date string to a readable format
 * 
 * @param string $dateString The date string to format
 * @param string $format The format to use (default: 'M d, Y')
 * @return string The formatted date
 */
function formatDate($dateString, $format = 'M d, Y') {
    $date = new DateTime($dateString);
    return $date->format($format);
}

/**
 * Generate a random string for reference numbers, etc.
 * 
 * @param int $length The length of the string to generate
 * @return string The generated string
 */
function generateRandomString($length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Format a number as currency
 * 
 * @param float $amount The amount to format
 * @param string $currency The currency symbol (default: '$')
 * @return string The formatted amount
 */
function formatCurrency($amount, $currency = '$') {
    return $currency . number_format($amount, 2, '.', ',');
} 