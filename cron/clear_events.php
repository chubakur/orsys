<?php
require_once(__DATE__.'/../config.php');
require_once(__DATE__.'/../events.php');
echo remove_old_events($config['mysql']['events']);