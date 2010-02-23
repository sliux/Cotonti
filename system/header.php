<?php
/**
 * Global header
 *
 * @package Cotonti
 * @version 0.7.0
 * @author Neocrome, Cotonti Team
 * @copyright Copyright (c) Cotonti Team 2008-2010
 * @license BSD
 */

defined('SED_CODE') or die('Wrong URL');

sed_uriredir_store();

/* === Hook === */
$extp = sed_getextplugins('header.first');
foreach ($extp as $pl)
{
	include $pl;
}
/* ===== */

/* ======== Who's online (part 3) ======== */
if (!$cfg['disablewhosonline'])
{
	if ($location != $sys['online_location']
		|| !empty($sys['sublocaction']) && $sys['sublocaction'] != $sys['online_subloc'])
	{
		if ($usr['id'] > 0)
		{
			if (empty($sys['online_location']))
			{
				sed_sql_query("INSERT INTO $db_online (online_ip, online_name, online_lastseen, online_location, online_subloc, online_userid, online_shield, online_hammer)
					VALUES ('".$usr['ip']."', '".sed_sql_prep($usr['name'])."', ".(int)$sys['now'].", '".sed_sql_prep($location)."',  '".sed_sql_prep($sys['sublocation'])."', ".(int)$usr['id'].", 0, 0)");
			}
			else
			{
				sed_sql_query("UPDATE $db_online SET online_lastseen='".$sys['now']."', online_location='".sed_sql_prep($location)."', online_subloc='".sed_sql_prep($sys['sublocation'])."', online_hammer=".(int)$sys['online_hammer']." WHERE online_userid=".$usr['id']);
			}
		}
		else
		{
			if (empty($sys['online_location']))
			{
				sed_sql_query("INSERT INTO $db_online (online_ip, online_name, online_lastseen, online_location, online_subloc, online_userid, online_shield, online_hammer)
					VALUES ('".$usr['ip']."', 'v', ".(int)$sys['now'].", '".sed_sql_prep($location)."', '".sed_sql_prep($sys['sublocation'])."', -1, 0, 0)");
			}
			else
			{
				sed_sql_query("UPDATE $db_online SET online_lastseen='".$sys['now']."', online_location='".$location."', online_subloc='".sed_sql_prep($sys['sublocation'])."', online_hammer=".(int)$sys['online_hammer']." WHERE online_ip='".$usr['ip']."'");
			}
		}
	}
	if ($cot_cache && $cot_cache->mem_available && $cot_cache->mem_isset('whosonline', 'system'))
	{
		$whosonline_data = $cot_cache->mem_get('whosonline', 'system');
		$sys['whosonline_vis_count'] = $whosonline_data['vis_count'];
		$sys['whosonline_reg_count'] = $whosonline_data['reg_count'];
		$out['whosonline_reg_list'] = $whosonline_data['reg_list'];
		unset($whosonline_data);
	}
	else
	{
		sed_sql_query("DELETE FROM $db_online WHERE online_lastseen<$online_timedout");
		$sys['whosonline_vis_count'] = sed_sql_result(sed_sql_query("SELECT COUNT(*) FROM $db_online WHERE online_name='v'"), 0, 0);
		$sql_o = sed_sql_query("SELECT DISTINCT o.online_name, o.online_userid FROM $db_online o WHERE o.online_name != 'v' ORDER BY online_name ASC");
		$sys['whosonline_reg_count'] = sed_sql_numrows($sql_o);
		$ii_o = 0;
		while ($row_o = sed_sql_fetcharray($sql_o))
		{
			$out['whosonline_reg_list'] .= ($ii_o > 0) ? ', ' : '';
			$out['whosonline_reg_list'] .= sed_build_user($row_o['online_userid'], htmlspecialchars($row_o['online_name']));
			$sed_usersonline[] = $row_o['online_userid'];
			$ii_o++;
		}
		sed_sql_freeresult($sql_o);
		unset($ii_o, $sql_o, $row_o);
		if ($cot_cache && $cot_cache->mem_available)
		{
			$whosonline_data = array(
				'vis_count' => $sys['whosonline_vis_count'],
				'reg_count' => $sys['whosonline_reg_count'],
				'reg_list' => $out['whosonline_reg_list']
			);
			$cot_cache->mem_set('whosonline', $whosonline_data, 'system', 30);
		}
	}
	$sys['whosonline_all_count'] = $sys['whosonline_reg_count'] + $sys['whosonline_vis_count'];
}


$out['logstatus'] = ($usr['id'] > 0) ? $L['hea_youareloggedas'].' '.$usr['name'] : $L['hea_youarenotlogged'];
$out['userlist'] = (sed_auth('users', 'a', 'R')) ? sed_rc_link(sed_url('users'), $L['Users']) : '';
$out['compopup'] = sed_javascript($morejavascript);

unset($title_tags, $title_data);
$title_params = array(
	'MAINTITLE' => $cfg['maintitle'],
	'DESCRIPTION' => $cfg['subtitle'],
	'SUBTITLE' => $out['subtitle']
);
if (defined('SED_INDEX'))
{
	$out['fulltitle'] = sed_title('title_header_index', $title_params);
}
else
{
	$out['fulltitle'] = sed_title('title_header', $title_params);
}

$out['meta_contenttype'] = ($cfg['doctypeid'] > 2 && $cfg['xmlclient']) ? "application/xhtml+xml" : "text/html";
$out['basehref'] = $R['code_basehref'];
$out['meta_charset'] = $cfg['charset'];
$out['meta_desc'] = htmlspecialchars($out['desc']);
$out['meta_keywords'] = empty($out['keywords']) ? $cfg['metakeywords'] : htmlspecialchars($out['keywords']);
$out['meta_lastmod'] = gmdate('D, d M Y H:i:s');
$out['head_head'] = $out['head'];

sed_sendheaders();

if (!SED_AJAX)
{
	if ($usr['id'] > 0 && !$cfg['disable_page'] && sed_auth('page', 'any', 'A'))
	{
		$sqltmp2 = sed_sql_query("SELECT COUNT(*) FROM $db_pages WHERE page_state=1");
		$sys['pagesqueued'] = sed_sql_result($sqltmp2, 0, 'COUNT(*)');

		if ($sys['pagesqueued'] > 0)
		{
			$out['notices'] .= $L['hea_valqueues'];

			if ($sys['pagesqueued'] == 1)
			{
				$out['notices'] .= sed_rc_link(sed_url('admin', 'm=page'), '1 ' . $L['Page']);
			}
			elseif ($sys['pagesqueued'] > 1)
			{
				$out['notices'] .= sed_rc_link(sed_url('admin', 'm=page'), $sys['pagesqueued'] . ' ' . $L['Pages']);
			}
		}
	}
	elseif ($usr['id'] > 0 && !$cfg['disable_page'] && sed_auth('page', 'any', 'W'))
	{
		$sqltmp2 = sed_sql_query("SELECT COUNT(*) FROM $db_pages WHERE page_state=1 AND page_ownerid = " . $usr['id']);
		$sys['pagesqueued'] = sed_sql_result($sqltmp2, 0, 'COUNT(*)');

		if ($sys['pagesqueued'] > 0)
		{
			$out['notices'] .= $L['hea_valqueues'];

			if ($sys['pagesqueued'] == 1)
			{
				$out['notices'] .= sed_rc_link(sed_url('list', 'c=unvalidated'), '1 ' . $L['Page']);
			}
			elseif ($sys['pagesqueued'] > 1)
			{
				$out['notices'] .= sed_rc_link(sed_url('list', 'c=unvalidated'), $sys['pagesqueued'] . ' ' . $L['Pages']);
			}
		}
	}

	/* === Hook === */
	$extp = sed_getextplugins('header.main');
	foreach ($extp as $pl)
	{
		include $pl;
	}
	/* ===== */

	$mskin = sed_skinfile($cfg['enablecustomhf'] ? array('header', mb_strtolower($location)) : 'header', '+', defined('SED_ADMIN'));
	$t = new XTemplate($mskin);

	$t->assign(array(
		'HEADER_TITLE' => $plug_title . $out['fulltitle'],
		'HEADER_DOCTYPE' => $cfg['doctype'],
		'HEADER_CSS' => $cfg['css'],
		'HEADER_COMPOPUP' => $out['compopup'],
		'HEADER_LOGSTATUS' => $out['logstatus'],
		'HEADER_WHOSONLINE' => $out['whosonline'],
		'HEADER_TOPLINE' => $cfg['topline'],
		'HEADER_BANNER' => $cfg['banner'],
		'HEADER_GMTTIME' => $usr['gmttime'],
		'HEADER_USERLIST' => $out['userlist'],
		'HEADER_NOTICES' => $out['notices'],
		'HEADER_BASEHREF' => $out['basehref'],
		'HEADER_META_CONTENTTYPE' => $out['meta_contenttype'],
		'HEADER_META_CHARSET' => $out['meta_charset'],
		'HEADER_META_DESCRIPTION' => $out['meta_desc'],
		'HEADER_META_KEYWORDS' => $out['meta_keywords'],
		'HEADER_META_LASTMODIFIED' => $out['meta_lastmod'],
		'HEADER_HEAD' => $out['head_head']
	));

	/* === Hook === */
	$extp = sed_getextplugins('header.body');
	foreach ($extp as $pl)
	{
		include $pl;
	}
	/* ===== */

	if ($usr['id'] > 0)
	{
		$out['adminpanel'] = (sed_auth('admin', 'any', 'R')) ? sed_rc_link(sed_url('admin'), $L['Administration']) : '';
		$out['loginout_url'] = sed_url('users', 'm=logout&' . sed_xg());
		$out['loginout'] = sed_rc_link($out['loginout_url'], $L['Logout']);
		$out['profile'] = sed_rc_link(sed_url('users', 'm=profile'), $L['Profile']);
		$out['pms'] = ($cfg['disable_pm']) ? '' : sed_rc_link(sed_url('pm'), $L['Private_Messages']);
		$out['pfs'] = ($cfg['disable_pfs'] || !sed_auth('pfs', 'a', 'R') || $sed_groups[$usr['maingrp']]['pfs_maxtotal'] == 0 || $sed_groups[$usr['maingrp']]['pfs_maxfile'] == 0) ? '' : sed_rc_link(sed_url('pfs'), $L['Mypfs']);

		if (!$cfg['disable_pm'])
		{
			if ($usr['newpm'])
			{
				$sqlpm = sed_sql_query("SELECT COUNT(*) FROM $db_pm WHERE pm_touserid='".$usr['id']."' AND pm_tostate=0");
				$usr['messages'] = sed_sql_result($sqlpm, 0, 'COUNT(*)');
			}
			$out['pmreminder'] = sed_rc_link(sed_url('pm'),
				($usr['messages'] > 0) ? sed_declension($usr['messages'], $Ls['Privatemessages']) : $L['hea_noprivatemessages']
			);
		}

		$t->assign(array(
			'HEADER_USER_NAME' => $usr['name'],
			'HEADER_USER_ADMINPANEL' => $out['adminpanel'],
			'HEADER_USER_LOGINOUT' => $out['loginout'],
			'HEADER_USER_PROFILE' => $out['profile'],
			'HEADER_USER_PMS' => $out['pms'],
			'HEADER_USER_PFS' => $out['pfs'],
			'HEADER_USER_PMREMINDER' => $out['pmreminder'],
			'HEADER_USER_MESSAGES' => $usr['messages']
		));

		$t->parse('HEADER.USER');
	}
	else
	{
		$out['guest_username'] = $R['form_guest_username'];
		$out['guest_password'] = $R['form_guest_password'];
		$out['guest_register'] = sed_rc_link(sed_url('users', 'm=register'), $L['Register']);
		$out['guest_cookiettl'] = $R['form_guest_remember'];

		$t->assign(array (
			'HEADER_GUEST_SEND' => sed_url('users', 'm=auth&a=check&' . $sys['url_redirect']),
			'HEADER_GUEST_USERNAME' => $out['guest_username'],
			'HEADER_GUEST_PASSWORD' => $out['guest_password'],
			'HEADER_GUEST_REGISTER' => $out['guest_register'],
			'HEADER_GUEST_COOKIETTL' => $out['guest_cookiettl']
		));

		$t->parse('HEADER.GUEST');
	}

	/* === Hook === */
	$extp = sed_getextplugins('header.tags');
	foreach ($extp as $pl)
	{
		include $pl;
	}
	/* ===== */

	$t->parse('HEADER');
	$t->out('HEADER');
}
?>