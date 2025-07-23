<?php

declare(strict_types=1);

function get_email(object $pdo, string $email)
{
    $query = "SELECT email FROM students WHERE email = :email;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function get_student_number(object $pdo, string $student_number)
{
    $query = "SELECT student_number FROM students WHERE student_number = :student_number;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":student_number", $student_number);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function set_user(object $pdo, string $student_number, string $name, string $email, string $pass)
{
    $query = "INSERT INTO students (student_number, name, email, password) VALUES (:student_number, :name, :email, :password);";
    $stmt = $pdo->prepare($query);

    $options = [
        'cost' => 12,
    ];
    $hashedPassword = password_hash($pass, PASSWORD_BCRYPT, $options);

    $stmt->bindParam(":student_number", $student_number);
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $hashedPassword);
    $stmt->execute();


}
