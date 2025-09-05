---
title: Security Sniffs Reference
---

# Security Sniffs

The ArtisanPack UI Code Standards package includes security-focused sniffs that help identify potential security vulnerabilities in your PHP code. These sniffs are designed to catch common security issues and promote secure coding practices.

## Available Security Sniffs

### ValidatedSanitizedInputSniff

Ensures that user input is properly validated and sanitized before use. This sniff helps prevent various injection attacks and data validation issues.

**What it checks:**
- User input from `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`, and `$_SERVER` is validated
- Input sanitization functions are used appropriately
- Direct use of superglobals without validation

**Example:**
```php
// Incorrect - Direct use of user input
$username = $_POST['username'];
$query = "SELECT * FROM users WHERE username = '$username'";

// Correct - Validated and sanitized input
$username = sanitize_user($_POST['username']);
if (validate_username($username)) {
    $query = $wpdb->prepare("SELECT * FROM users WHERE username = %s", $username);
}
```

**Common validation functions recognized:**
- `sanitize_text_field()`
- `sanitize_email()`
- `sanitize_url()`
- `intval()`
- `floatval()`
- `absint()`
- `wp_verify_nonce()`

### EscapeOutputSniff

Ensures that all output is properly escaped to prevent Cross-Site Scripting (XSS) attacks. This is crucial for web applications that display user-generated content.

**What it checks:**
- `echo` and `print` statements use proper escaping functions
- Variables in HTML contexts are escaped
- Attribute values are properly escaped
- URLs are escaped when output

**Example:**
```php
// Incorrect - Unescaped output
echo $user_input;
echo '<div class="' . $css_class . '">';
echo '<a href="' . $url . '">Link</a>';

// Correct - Properly escaped output
echo esc_html($user_input);
echo '<div class="' . esc_attr($css_class) . '">';
echo '<a href="' . esc_url($url) . '">Link</a>';
```

**Recognized escaping functions:**
- `esc_html()` - For HTML content
- `esc_attr()` - For HTML attributes
- `esc_url()` - For URLs
- `esc_js()` - For JavaScript
- `esc_textarea()` - For textarea content
- `wp_kses()` - For allowed HTML
- `wp_kses_post()` - For post content

## Security Best Practices

### Input Validation
Always validate user input at the point of entry:

```php
// Good practice
if (isset($_POST['email']) && is_email($_POST['email'])) {
    $email = sanitize_email($_POST['email']);
    // Process email
}
```

### Output Escaping
Escape output based on context:

```php
// HTML context
echo '<p>' . esc_html($content) . '</p>';

// Attribute context
echo '<input value="' . esc_attr($value) . '">';

// URL context
echo '<a href="' . esc_url($link) . '">Click here</a>';

// JavaScript context
echo '<script>var data = "' . esc_js($data) . '";</script>';
```

### Nonce Verification
Always verify nonces for forms and AJAX requests:

```php
// Form processing
if (!wp_verify_nonce($_POST['_wpnonce'], 'my_action')) {
    wp_die('Security check failed');
}

// AJAX processing
if (!wp_verify_nonce($_POST['nonce'], 'ajax_nonce')) {
    wp_die('Security check failed');
}
```

## Customizing Security Sniffs

You can customize the behavior of security sniffs in your `phpcs.xml` configuration:

### ValidatedSanitizedInput Customization

```xml
<rule ref="ArtisanPackUI.Security.ValidatedSanitizedInput">
    <properties>
        <!-- Additional validation functions to recognize -->
        <property name="validationFunctions" type="array">
            <element value="my_custom_validator"/>
            <element value="another_validator"/>
        </property>
        
        <!-- Additional sanitization functions to recognize -->
        <property name="sanitizationFunctions" type="array">
            <element value="my_custom_sanitizer"/>
            <element value="another_sanitizer"/>
        </property>
        
        <!-- Superglobals to check (default: $_GET, $_POST, $_REQUEST, $_COOKIE, $_SERVER) -->
        <property name="superglobals" type="array">
            <element value="$_GET"/>
            <element value="$_POST"/>
            <element value="$_REQUEST"/>
        </property>
    </properties>
</rule>
```

### EscapeOutput Customization

```xml
<rule ref="ArtisanPackUI.Security.EscapeOutput">
    <properties>
        <!-- Additional escaping functions to recognize -->
        <property name="escapingFunctions" type="array">
            <element value="my_custom_escape"/>
            <element value="another_escape"/>
        </property>
        
        <!-- Output functions to check (default: echo, print, printf) -->
        <property name="outputFunctions" type="array">
            <element value="echo"/>
            <element value="print"/>
            <element value="printf"/>
        </property>
        
        <!-- Whether to allow some unescaped output in specific contexts -->
        <property name="allowRawOutput" value="false"/>
    </properties>
</rule>
```

## Disabling Security Sniffs

If you need to disable security sniffs (not recommended), you can do so in your `phpcs.xml`:

```xml
<!-- Disable specific security sniff -->
<rule ref="ArtisanPackUIStandard">
    <exclude name="ArtisanPackUI.Security.ValidatedSanitizedInput"/>
</rule>

<!-- Or disable all security sniffs -->
<rule ref="ArtisanPackUIStandard">
    <exclude name="ArtisanPackUI.Security"/>
</rule>
```

## Security Resources

For more information about PHP security best practices:

- [WordPress Security Handbook](https://developer.wordpress.org/advanced-administration/security/)
- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [WordPress Data Validation](https://developer.wordpress.org/plugins/security/data-validation/)
- [WordPress Sanitizing Data](https://developer.wordpress.org/plugins/security/sanitizing/)

## False Positives

Sometimes security sniffs may report false positives. Here's how to handle them:

### Inline Comments
Use phpcs comments to ignore specific lines:

```php
// phpcs:ignore ArtisanPackUI.Security.EscapeOutput.OutputNotEscaped
echo $safe_html; // This HTML is already escaped elsewhere
```

### Code Blocks
Ignore entire code blocks:

```php
// phpcs:disable ArtisanPackUI.Security.ValidatedSanitizedInput
$data = $_POST; // This is validated later in the function
// phpcs:enable ArtisanPackUI.Security.ValidatedSanitizedInput
```

**Note:** Use ignore comments sparingly and only when you're certain the code is secure. Always document why the security check is being bypassed.