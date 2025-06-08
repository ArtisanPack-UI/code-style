<?php

/**
 * Sample file to test the security sniffs.
 */

// Mock database object for testing purposes
class MockDB {
    public function where($column, $value) {
        return $this;
    }

    public function update($table, $data) {
        return true;
    }

    public function query($sql) {
        return [];
    }

    public function insert($table, $data) {
        return true;
    }
}

$db = new MockDB();

// Example 1: Unescaped output (should trigger EscapeOutputSniff)
function badEchoExample() {
    $userInput = $_GET['name'];
    echo $userInput; // This should trigger an error
    echo "Hello, " . $userInput; // This should also trigger an error
}

// Example 2: Properly escaped output (should not trigger EscapeOutputSniff)
function goodEchoExample() {
    $userInput = $_GET['name'];
    echo escape_html($userInput); // This should be fine
    echo "Hello, " . escape_html($userInput); // This should also be fine
}

// Example 3: Unsanitized input to database (should trigger ValidatedSanitizedInputSniff)
function badDatabaseExample() {
    global $db;
    $userId = $_POST['user_id'];
    $db->where('id', $userId); // This should trigger an error
    $db->update('users', ['name' => $_POST['name']]); // This should also trigger an error
}

// Example 4: Properly sanitized input to database (should not trigger ValidatedSanitizedInputSniff)
function goodDatabaseExample() {
    global $db;
    $userId = sanitize_number_int($_POST['user_id']);
    $db->where('id', $userId); // This should be fine
    $db->update('users', ['name' => sanitize_text($_POST['name'])]); // This should also be fine
}

// Example 5: Mixed case with both issues
function mixedExample() {
    global $db;
    $userInput = $_GET['query'];
    $results = $db->query("SELECT * FROM posts WHERE title LIKE '%" . $userInput . "%'"); // Should trigger sanitization error

    foreach ($results as $result) {
        echo $result->title; // Should trigger escaping error
        echo escape_html($result->content); // This should be fine
    }
}

// Example 6: Input function without sanitization
function inputFunctionExample() {
    global $db;
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $db->insert('subscribers', ['email' => $email]); // Should trigger sanitization error

    $name = request()->input('name');
    echo $name; // Should trigger escaping error
}

// Example 7: Properly handled input function
function goodInputFunctionExample() {
    global $db;
    $email = sanitize_email(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $db->insert('subscribers', ['email' => $email]); // This should be fine

    $name = sanitize_text(request()->input('name'));
    echo escape_html($name); // This should be fine
}
