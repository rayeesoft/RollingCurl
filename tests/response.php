<?php
file_put_contents('/tmp/rollcurl_test_response.log', var_export($_REQUEST,1)."\n", FILE_APPEND);
echo '1';