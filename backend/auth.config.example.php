<?php
/**
 * Example of the server-only auth config read by google_client_id() in
 * google_auth.php. Do NOT put the real file in this repository or anywhere
 * under public_html. On Hostinger, create it with File Manager at:
 *
 *     /home/<account>/domains/atmabiswas.org/private/auth.config.php
 *
 * i.e. a "private" folder next to public_html (outside the web root).
 * Alternatively set the GOOGLE_CLIENT_ID environment variable instead.
 *
 * The OAuth *client id* is public (it appears in the login page HTML); no
 * client secret is needed, because the server only verifies Google's signed
 * ID tokens. Leave the value empty to keep Google sign-in switched off.
 */
return [
    'google_client_id' => '123456789012-abcdefghijklmnopqrstuvwxyz012345.apps.googleusercontent.com',
];
