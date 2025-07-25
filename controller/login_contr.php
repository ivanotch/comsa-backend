<?php 

declare(strict_types=1);

function is_email_wrong(bool|array $result) {
    if (!$result) {
        return true;
    } else {
        return false;
    }
}

function is_pass_wrong(string $pass, string $hashedPass): bool {
    return $pass !== $hashedPass;
}

// function is_pass_wrong(string $pass, string $hashedPass) {
//     // if (!password_verify($pass, $hashedPass)) {
//     //     return true;
//     // } else {
//     //     return false;
//     // }

//     if (!$pass === $hashedPass) {
//         return true;
//     } else {
//         return false;
//     }
// }

function is_input_empty(string $email, string $pass): bool {
    return empty($email) || empty($pass);
}