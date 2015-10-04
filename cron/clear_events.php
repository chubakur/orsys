<?php
require_once(__DIR__.'/../config.php');
require_once(__DIR__.'/../events.php');
echo remove_old_events($config['mysql']['events']);