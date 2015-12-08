<?php


if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}


function check_ip_with_dnsbl_info()
{
	return array(
		"name" => "Check IP with DNSBLs",
		"description" => "Check IP upon registration in DNSBLs",
		"website" => "http://github.com/dequeues",
		"author" => "Nathan (dequeues)",
		"guid" => "",
		"version" => "1.0",
		"compatibility" => "18*"
	);
}

function check_ip_with_dnsbl_activate()
{

}

function check_ip_with_dnsbl_deactivate()
{

}

function getRealIP()
{
	$dev = false;
	if (!empty($_SERVER['HTTP_X_REAL_IP']))
	{
		return $_SERVER['HTTP_X_REAL_IP'];
	} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		return $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	return $_SERVER['REMOTE_ADDR'];
}

function check_ip()
{
	$realIP = getRealIP();
	if (is_in_dnsbl($realIP))
	{
		global $lang;
		require_once(MYBB_ROOT . "/inc/functions.php");
		error($lang->sprintf($lang->error_stop_forum_spam_spammer, "IP"));
	}
}

function reverseIP($ip)
{
	return implode('.', array_reverse(explode('.', $ip)));
}

function is_in_dnsbl($ip)
{
	$dnsbl_list = array("rbl.efnetrbl.org", "xbl.spamhaus.org", "tor.dnsbl.sectoor.de");
	$reverseIP = reverseIP($ip);

	foreach ($dnsbl_list as $dnsbl)
	{
		if(checkdnsrr($reverseIP . "." . $dnsbl. ".", 'A'))
		{
			return true;
		}
	}
	return false;
}

$plugins->add_hook("member_do_register_start", "check_ip");

