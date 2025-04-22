<!-- Logged out -->
<p><?php esc_html_e( 'You must have an account and be logged in to use this feature:', 'iqbible' ); ?></p>
<ul>
    <li><?php esc_html_e( 'Create, save, and edit notes', 'iqbible' ); ?></li>
    <li><?php esc_html_e( 'Save favorite verses', 'iqbible' ); ?></li>
</ul>
<?php
// Retrieve the custom login URL, with a fallback to wp_login_url()
// Use wp_login_url() directly if the option isn't set or is empty
$login_option_url = get_option('iq_bible_custom_login_url');
$custom_login_url = !empty($login_option_url) ? esc_url($login_option_url) : wp_login_url();

// Ensure the URL includes the redirect parameter if needed, pointing back to the current page
$redirect_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$final_login_url = add_query_arg( 'redirect_to', urlencode( $redirect_url ), $custom_login_url );

?>

<button class="iqbible-login-button"><a href="<?php echo esc_url($final_login_url); // Use esc_url again just to be safe ?>"><?php esc_html_e( 'Log in or create an account now!', 'iqbible' ); ?></a></button>