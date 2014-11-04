<?php

/* http://www.phptherightway.com/#php_and_utf8 */
// Tell PHP that we're using UTF-8 strings until the end of the script
mb_internal_encoding('UTF-8');

// Tell PHP that we'll be outputting UTF-8 to the browser
mb_http_output('UTF-8');

require '../app/config/config.php';

require '../app/lib/app.php';
require '../app/lib/controller.php';
require '../app/lib/model.php';

$app = new Scam\App();
$app->run();