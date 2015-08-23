<?php
	require_once 'class/WhatsBot.php';


	# Config
	$Debug = false;


	Std::Out('Starting Whatsborg...');

	$W = new WhatsBot($Debug);
	$W->Start();
	$W->Listen();