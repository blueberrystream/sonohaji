<?php
require_once('const.inc');

session_start();
$_SESSION[session_id()] = SESSION_VALUE;
session_commit();
