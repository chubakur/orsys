<?php
require('entry_point.php');
session_unset();
session_destroy();
die('{"status":"ok"}');