<?php
require_once('../config.php');
require_once('../events.php');
echo remove_old_events($config['mysql']['events']);