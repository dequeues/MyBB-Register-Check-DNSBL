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
		"author" => "<a href=\"http://github.com/dequeues\">Nathan (dequeues)</a>",
		"guid" => "",
		"version" => "2.0",
		"compatibility" => "18*"
	);
}

function check_ip_with_dnsbl_activate()
{
	global $db;

	$number_groups_query = $db->simple_select("settinggroups", "COUNT(*) AS num_groups");
	$number_groups = (int)$db->fetch_field($number_groups_query, "num_groups");

	$setting_group = array (
		"name" => "checkipwithdnsbl",
		"title" => "Check IP with DNSBLs",
		"description" => "Check IP with DNSBL(s) on registration",
		"disporder" => ((int)$number_groups + 1),
		"isdefault" => 0
	);
	$gid = $db->insert_query("settinggroups", $setting_group);

	$settings = array (
		"checkipwithdnsbl_enabled" => array (
			"title" => "Enabled?",
			"description" => "Check IP addresses on registration against enabled DNSBL(s)?",
			"optionscode" => "onoff",
			"value" => 1
		),
		"checkipwithdnsbl_allowtor" => array (
			"title" => "Allow Tor users?",
			"description" => "Allow people to register connecting through Tor? For more information on Tor, visit <a href=\"https://www.torproject.org/\">Tor Project</a>",
			"optionscode" => "yesno",
			"value" => 1
		),
		"checkipwithdnsbl_dnsbllist" => array (
			"title" => "DNSBL list",
			"description" => "A list of the DNSBLs to check IP addresses against before completing registration (one per line)",
			"optionscode" => "textarea",
			"value" => "rbl.efnetrbl.org\nxbl.spamhaus.org\ndnsbl.dronebl.org\nb.barracudacentral.org"
		)
	);

	$disporder = 1;
	foreach ($settings as $name => $setting)
	{
		$setting["name"] = $name;
		$setting["gid"] = $gid;
		$setting["disporder"] = $disporder;
		$db->insert_query("settings", $setting);
		$disporder++;
	}

	rebuild_settings();
}

function check_ip_with_dnsbl_deactivate()
{
	global $db;

	$db->delete_query("settinggroups", "name = 'checkipwithdnsbl'");
	$db->delete_query("settings", "name LIKE ('checkipwithdnsbl_%')");

	rebuild_settings();
}

function check_ip_with_dnsbl_is_activated()
{
	global $mybb;
	if ($mybb->settings['checkipwithdnsbl_enabled'] == "1")
	{
		return true;
	}
	return false;
}

function getRealIP()
{
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
	global $mybb;

	if (!check_ip_with_dnsbl_is_activated())
	{
		return;
	}

	$realIP = getRealIP();

	$is_listed = is_in_dnsbl($realIP);

	if (!$is_listed)
	{
		return;
	}

	$lang_var = str_replace(".", "_", $is_listed);

	global $lang;
	$lang->load("check_ip_with_dnsbl");
	require_once(MYBB_ROOT . "/inc/functions.php");

	if (isset($lang->{$lang_var}))
	{
		error($lang->sprintf($lang->{$lang_var}, $realIP));
	}
	else
	{
		$lang->load("member");
		error($lang->sprintf($lang->error_stop_forum_spam_spammer, "IP"));
	}
}

function reverseIP($ip)
{
	return implode('.', array_reverse(explode('.', $ip)));
}

function is_in_dnsbl($ip)
{
	global $mybb;

	$dnsbl_list = explode("\n", $mybb->settings['checkipwithdnsbl_dnsbllist']);

	$reverseIP = reverseIP($ip);

	foreach ($dnsbl_list as $dnsbl)
	{
		if(checkdnsrr("{$reverseIP}.{$dnsbl}.", 'A'))
		{
			return $dnsbl;
		}
	}

	if ($mybb->settings['checkipwithdnsbl_allowtor'] == "0")
	{
		if (checkdnsrr("{$reverseIP}.tor.dnsbl.sectoor.de.", 'A'))
		{
			return "tor.dnsbl.sectoor.de";
		}
	}

	return false;
}

$plugins->add_hook("member_do_register_start", "check_ip");
