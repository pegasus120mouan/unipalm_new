<?php
// Function to check password complexity
function isPasswordComplex($password, $retype_password) {
    // Define password complexity requirements
    $min_password_length = 8;
    $uppercase_required = true;
    $lowercase_required = true;
    $number_required = true;
    $special_character_required = true;

    // Regular expressions for character types
    $uppercase_regex = '/[A-Z]/';
    $lowercase_regex = '/[a-z]/';
    $number_regex = '/[0-9]/';
    $special_character_regex = '/[^A-Za-z0-9]/'; // Matches any character that is not a letter or a number

    // Check password complexity
    if (
        $password != $retype_password ||
        strlen($password) < $min_password_length ||
        ($uppercase_required && !preg_match($uppercase_regex, $password)) ||
        ($lowercase_required && !preg_match($lowercase_regex, $password)) ||
        ($number_required && !preg_match($number_regex, $password)) ||
        ($special_character_required && !preg_match($special_character_regex, $password))
    ) {
        return false;
    }

    return true;
}

function isPhoneNumberValid($contact, $min_length = 10, $max_length = 10) {
    // Remove formatting characters if present
    $contact = preg_replace('/[^0-9]/', '', $contact);

    // Check phone number length
    $contact_length = strlen($contact);
    if ($contact_length < $min_length || $contact_length > $max_length) {
        return false;
    }

    return true;
}


function isContactNumberValid($contact, $min_length = 10, $max_length = 10) {
    // Remove formatting characters if present
    $contact_boutique = preg_replace('/[^0-9]/', '', $contact_boutique);

    // Check phone number length
    $contact_length_boutique = strlen($contact_boutique);
    if ($contact_length_boutique < $min_length || $contact_length_boutique > $max_length) {
        return false;
    }

    return true;
}
?>
