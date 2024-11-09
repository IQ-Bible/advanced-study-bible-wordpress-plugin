<!-- Logged out -->
<p>You must have an account and be logged in to use this feature:</p>
<ul>
    <li>Create, save, and edit notes</li>
    <li>Save favorite verses</li>
</ul>
<?php
// Retrieve the custom login URL, with a fallback to wp_login_url()
$custom_login_url = esc_url(get_option('iq_bible_custom_login_url', wp_login_url()));
?>

<button><a href="<?php echo $custom_login_url; ?>">Log in or create an account now!</a></button>