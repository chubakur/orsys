<?php
session_start();
session_unset();
session_destroy();
die('{"status":"ok"}');