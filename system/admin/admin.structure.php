<?php
/**
 * Administration panel
 *
 * @package Cotonti
 * @version 0.7.0
 * @author Neocrome, Cotonti Team
 * @copyright Copyright (c) Cotonti Team 2008-2010
 * @license BSD
 */

(defined('SED_CODE') && defined('SED_ADMIN')) or die('Wrong URL.');

list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = sed_auth('admin', 'a');
sed_block($usr['isadmin']);

require_once sed_incfile('extrafields');
require_once sed_incfile('auth');

$t = new XTemplate(sed_skinfile('admin.structure'));

require_once sed_incfile('forms');

$adminpath[] = array (sed_url('admin', 'm=structure'), $L['Categories']);
$adminhelp = $L['adm_help_structure'];

$id = sed_import('id', 'G', 'INT');
$c = sed_import('c', 'G', 'TXT');
$d = sed_import('d', 'G', 'INT');
$d = empty($d) ? 0 : (int) $d;

/* === Hook === */
$extp = sed_getextplugins('admin.structure.first');
foreach ($extp as $pl)
{
	include $pl;
}
/* ===== */

$options_sort = array(
	'id' => $L['Id'],
	'type' => $L['Type'],
	'key' => $L['Key'],
	'title' => $L['Title'],
	'desc' => $L['Description'],
	'text' => $L['Body'],
	'author' => $L['Author'],
	'ownerid' => $L['Owner'],
	'date' => $L['Date'],
	'begin' => $L['Begin'],
	'expire' => $L['Expire'],
	'rating' => $L['Rating'],
	'count' => $L['Hits'],
	'comcount' => $L['Comments'],
	'file' => $L['adm_fileyesno'],
	'url' => $L['adm_fileurl'],
	'size' => $L['adm_filesize'],
	'filecount' => $L['adm_filecount']
);

// Extra fields pages
$extrafields = array();
foreach($sed_extrafields['pages'] as $i => $row)
{
	$$extrafields[$row['field_name']] = isset($L['page_'.$row['field_name'].'_title']) ? $L['page_'.$row['field_name'].'_title'] : $row['field_description'];
}
$options_sort = ($options_sort + $extrafields);

$options_way = array(
	'asc' => $L['Ascending'],
	'desc' => $L['Descending']
);

if ($n == 'options')
{
	if ($a == 'update')
	{
		$rcode = sed_import('rcode', 'P', 'TXT');
		$rpath = sed_import('rpath', 'P', 'TXT');
		$rtitle = sed_import('rtitle', 'P', 'TXT');
		$rtplmode = sed_import('rtplmode', 'P', 'INT');
		$rdesc = sed_import('rdesc', 'P', 'TXT');
		$ricon = sed_import('ricon', 'P', 'TXT');
		$rgroup = sed_import('rgroup', 'P', 'BOL');
		$rgroup = ($rgroup) ? 1 : 0;
		$rorder = sed_import('rorder', 'P', 'ALP');
		$rway = sed_import('rway', 'P', 'ALP');
		$rallowcomments = sed_import('rallowcomments', 'P', 'BOL');
		$rallowratings = sed_import('rallowratings', 'P', 'BOL');

		// Extra fields
		foreach ($sed_extrafields['structure'] as $row)
		{
			$import = sed_import('rstructure'.$row['field_name'], 'P', 'HTM');
			if ($row['field_type'] == 'checkbox')
			{
				$import = $import != '';
			}
			$rstructureextrafields[$row['field_name']] = $import;
		}

		$sqql = sed_sql_query("SELECT structure_code FROM $db_structure WHERE structure_id='".$id."' ");
		$roww = sed_sql_fetcharray($sqql);

		/* === Hook === */
		$extp = sed_getextplugins('admin.structure.options.update');
		foreach ($extp as $pl)
		{
			include $pl;
		}
		/* ===== */

		if ($roww['structure_code'] != $rcode)
		{

			$sql = sed_sql_query("UPDATE $db_structure SET structure_code='".sed_sql_prep($rcode)."' WHERE structure_code='".sed_sql_prep($roww['structure_code'])."' ");
			$sql = sed_sql_query("DELETE FROM $db_cache WHERE c_name='".sed_sql_prep($roww['structure_code'])."' ");
			$sql = sed_sql_query("UPDATE $db_auth SET auth_option='".sed_sql_prep($rcode)."' WHERE auth_code='page' AND auth_option='".sed_sql_prep($roww['structure_code'])."' ");
			$sql = sed_sql_query("UPDATE $db_pages SET page_cat='".sed_sql_prep($rcode)."' WHERE page_cat='".sed_sql_prep($roww['structure_code'])."' ");

			sed_auth_reorder();
			sed_auth_clear('all');
			$cot_cache && $cot_cache->db->remove('sed_cat', 'system');
		}

		if ($rtplmode == 1)
		{
			$rtpl = '';
		}
		elseif ($rtplmode == 3)
		{
			$rtpl = 'same_as_parent';
		}
		else
		{
			$rtpl = sed_import('rtplforced', 'P', 'ALP');
		}

		$sqltxt = "UPDATE $db_structure
			SET structure_path='".sed_sql_prep($rpath)."',
				structure_tpl='".sed_sql_prep($rtpl)."',
				structure_title='".sed_sql_prep($rtitle)."',
				structure_desc='".sed_sql_prep($rdesc)."',
				structure_icon='".sed_sql_prep($ricon)."',
				structure_group='".$rgroup."',
				structure_order='".sed_sql_prep($rorder.".".$rway)."',";

		// Extra fields
		foreach ($sed_extrafields['structure'] as $i => $fildname)
		{
			if (!is_null($rstructureextrafields[$i]))
			{
				$sqltxt .= "structure_".sed_sql_prep($fildname['field_name'])."='".sed_sql_prep($rstructureextrafields[$i])."',";
			}
		}

		$sqltxt .= "
				structure_comments='".$rallowcomments."',
				structure_ratings='".$rallowratings."'
			WHERE structure_id='".$id."'";
		$sql = sed_sql_query($sqltxt);

		if ($cot_cache)
		{
			$cot_cache->db->remove('sed_cat', 'system');
			if ($cfg['cache_page'])
			{
				$cot_cache->page->clear('page');
			}
		}

		sed_redirect(sed_url('admin', 'm=structure&d='.$d.$additionsforurl, '', true));
	}
	elseif ($a == 'resync')
	{
		sed_check_xg();

		$adminwarnings = sed_structure_resync($id) ? $L['Resynced'] : $L['Error'];

		if ($cot_cache && $cfg['cache_page'])
		{
			$cot_cache->page->clear('page');
		}
	}

	$sql = sed_sql_query("SELECT * FROM $db_structure WHERE structure_id='$id' LIMIT 1");
	sed_die(sed_sql_numrows($sql) == 0);

	$handle = opendir('./skins/'.$cfg['defaultskin'].'/');
	$allskinfiles = array();

	while ($f = readdir($handle))
	{
		if (($f != '.') && ($f != '..') && mb_strtolower(mb_substr($f, mb_strrpos($f, '.') + 1, 4)) == 'tpl')
		{
			$allskinfiles[] = $f;
		}
	}
	closedir($handle);

	$allskinfiles = implode(',', $allskinfiles);

	$row = sed_sql_fetcharray($sql);

	$structure_id = $row['structure_id'];
	$structure_code = $row['structure_code'];
	$structure_path = $row['structure_path'];
	$structure_title = $row['structure_title'];
	$structure_desc = $row['structure_desc'];
	$structure_icon = $row['structure_icon'];
	$structure_group = $row['structure_group'];
	$structure_comments = $row['structure_comments'];
	$structure_ratings = $row['structure_ratings'];
	$raw = explode('.', $row['structure_order']);
	$sort = $raw[0];
	$way = $raw[1];

	reset($options_sort);
	reset($options_way);

	if (empty($row['structure_tpl']))
	{
		$check_tpl = "1";
	}
	elseif ($row['structure_tpl'] == 'same_as_parent')
	{
		$structure_tpl_sym = "*";
		$check_tpl = "2";
	}
	else
	{
		$structure_tpl_sym = "+";
		$check_tpl = "3";
	}

	$adminpath[] = array (sed_url('admin', "m=structure&n=options&id=".$id), htmlspecialchars($structure_title));

	foreach ($sed_cat as $i => $x)
	{
		if ($i != 'all')
		{
			$cat_path[$i] = $x['tpath'];
		}
	}
	$cat_selectbox = sed_selectbox($row['structure_tpl'], 'rtplforced', array_keys($cat_path), array_values($cat_path), false);

	$t->assign(array(
		'ADMIN_STRUCTURE_UPDATE_FORM_URL' => sed_url('admin', 'm=structure&n=options&a=update&id='.$structure_id.'&d='.$d.'&'.sed_xg()),
		'ADMIN_STRUCTURE_CODE' => sed_inputbox('text', 'rcode', $structure_code, 'size="16"'),
		'ADMIN_STRUCTURE_PATH' => sed_inputbox('text', 'rpath', $structure_path, 'size="16" maxlength="16"'),
		'ADMIN_STRUCTURE_TITLE' => sed_inputbox('text', 'rtitle', $structure_title, 'size="64" maxlength="100"'),
		'ADMIN_STRUCTURE_DESC' => sed_inputbox('text', 'rdesc', $structure_desc, 'size="64" maxlength="255"'),
		'ADMIN_STRUCTURE_ICON' => sed_inputbox('text', 'ricon', $structure_icon, 'size="64" maxlength="128"'),
		'ADMIN_STRUCTURE_GROUP' => sed_checkbox(($structure_pages || $structure_group), 'rgroup'),
		'ADMIN_STRUCTURE_SELECT' => $cat_selectbox,
		'ADMIN_STRUCTURE_TPLMODE' => sed_radiobox($check_tpl, 'rtplmode', array('1'. '2', '3'), array($L['adm_tpl_empty'], $L['adm_tpl_forced'].'  '.$cat_selectbox, $L['adm_tpl_parent']), '', '<br />'),
		'ADMIN_STRUCTURE_WAY' => sed_selectbox($way, 'rway', array_keys($options_way), array_values($options_way), false),
		'ADMIN_STRUCTURE_ORDER' => sed_selectbox($sort, 'rorder', array_keys($options_sort), array_values($options_sort), false),
		'ADMIN_STRUCTURE_COMMENTS' => sed_radiobox($structure_comments, 'rallowcomments', array(1, 0), array($L['Yes'], $L['No'])),
		'ADMIN_STRUCTURE_RATINGS' => sed_radiobox($structure_ratings, 'rallowratings', array(1, 0), array($L['Yes'], $L['No'])),
		'ADMIN_STRUCTURE_RESYNC' => sed_url('admin', 'm=structure&n=options&a=resync&id='.$structure_id.'&'.sed_xg()),
	));

	// Extra fields
	foreach($sed_extrafields['structure'] as $i => $row2)
	{
		$uname = strtoupper($row['field_name']);
		$t->assign('ADMIN_STRUCTURE_'.$uname, sed_build_extrafields('structure',  $row2, $row['structure_'.$row2['field_name']]));
		$t->assign('ADMIN_STRUCTURE_'.$uname.'_TITLE', isset($L['structure_'.$row2['field_name'].'_title']) ?  $L['structure_'.$row2['field_name'].'_title'] : $row2['field_description']);

		// extra fields universal tags
		$t->assign('ADMIN_STRUCTURE_EXTRAFLD', sed_build_extrafields('structure',  $row2, $row['structure_'.$row2['field_name']]));
		$t->assign('ADMIN_STRUCTURE_EXTRAFLD_TITLE', isset($L['structure_'.$row2['field_name'].'_title']) ?  $L['structure_'.$row2['field_name'].'_title'] : $row2['field_description']);
		$t->parse('MAIN.OPTIONS.EXTRAFLD');
	}

	/* === Hook === */
	$extp = sed_getextplugins('admin.structure.options.tags');
	foreach ($extp as $pl)
	{
		include $pl;
	}
	/* ===== */
	$t->parse('MAIN.OPTIONS');
}
else
{
	if ($a == 'update')
	{
		$s = sed_import('s', 'P', 'ARR');

		foreach ($s as $i => $k)
		{
			$s[$i]['rgroup'] = (isset($s[$i]['rgroup'])) ? 1 : 0;
			// Extra fields
			foreach ($sed_extrafields['structure'] as $row)
			{
				$import = $s[$i]['rstructure'.$row['field_name']];
				if ($row['field_type'] == 'checkbox')
				{
					$import = $import != '';
				}
				$rstructureextrafields[$row['field_name']] = $import;
			}


			$sqql = sed_sql_query("SELECT structure_code FROM $db_structure WHERE structure_id='".$i."' ");
			$roww = sed_sql_fetcharray($sqql);

			/* === Hook === */
			$extp = sed_getextplugins('admin.structure.update');
			foreach ($extp as $pl)
			{
				include $pl;
			}
			/* ===== */

			if ($roww['structure_code'] != $s[$i]['rcode'])
			{
				$sql = sed_sql_query("UPDATE $db_structure SET structure_code='".sed_sql_prep($s[$i]['rcode'])."' WHERE structure_code='".sed_sql_prep($roww['structure_code'])."' ");
				$sql = sed_sql_query("DELETE FROM $db_cache WHERE c_name='".sed_sql_prep($roww['structure_code'])."' ");
				$sql = sed_sql_query("UPDATE $db_auth SET auth_option='".sed_sql_prep($s[$i]['rcode'])."' WHERE auth_code='page' AND auth_option='".sed_sql_prep($roww['structure_code'])."' ");
				$sql = sed_sql_query("UPDATE $db_pages SET page_cat='".sed_sql_prep($s[$i]['rcode'])."' WHERE page_cat='".sed_sql_prep($roww['structure_code'])."' ");

				sed_auth_reorder();
				sed_auth_clear('all');
			}

			$sql1text = "UPDATE $db_structure
				SET ";

			// Extra fields
			foreach ($sed_extrafields['structure'] as $j => $fildname)
			{
				if (!is_null($rstructureextrafields[$j]))
				{
					$sql1text .= "structure_".sed_sql_prep($fildname['field_name'])."='".sed_sql_prep($rstructureextrafields[$j])."',";
				}
			}

			$sql1text .= "
					structure_path='".sed_sql_prep($s[$i]['rpath'])."',
					structure_title='".sed_sql_prep($s[$i]['rtitle'])."',
					structure_order='".sed_sql_prep($s[$i]['rorder'].".".$s[$i]['rway'])."',
					structure_group='".$s[$i]['rgroup']."'
				WHERE structure_id='".$i."'";
			$sql1 = sed_sql_query($sql1text);
		}

		sed_auth_clear('all');
		if ($cot_cache)
		{
			$cot_cache->db->remove('sed_cat', 'system');
			if ($cfg['cache_page'])
			{
				$cot_cache->page->clear('page');
			}
		}

		$adminwarnings = $L['Updated'];
	}
	elseif ($a == 'add')
	{
		$g = array ('ncode', 'npath', 'ntitle', 'ndesc', 'nicon', 'ngroup', 'norder', 'nway');
		foreach ($g as $k => $x)
		{
			$$x = $_POST[$x];
		}
		$ngroup = (isset($ngroup)) ? 1 : 0;

		// Extra fields
		foreach ($sed_extrafields['structure'] as $row)
		{
			$import = sed_import('newstructure'.$row['field_name'], 'P', 'HTM');
			if ($row['field_type'] == 'checkbox')
			{
				$import = $import != '';
			}
			$rstructureextrafields[$row['field_name']] = $import;
		}

		/* === Hook === */
		$extp = sed_getextplugins('admin.structure.add');
		foreach ($extp as $pl)
		{
			include $pl;
		}
		/* ===== */

		$adminwarnings = (sed_structure_newcat($ncode, $npath, $ntitle, $ndesc, $nicon, $ngroup, $norder, $nway, $rstructureextrafields)) ? $L['Added'] : $L['Error'];

		if ($cot_cache && $cfg['cache_page'])
		{
			$cot_cache->page->clear('page');
		}
	}
	elseif ($a == 'delete')
	{
		sed_check_xg();

		/* === Hook === */
		$extp = sed_getextplugins('admin.structure.delete');
		foreach ($extp as $pl)
		{
			include $pl;
		}
		/* ===== */

		sed_structure_delcat($id, $c);

		if ($cot_cache && $cfg['cache_page'])
		{
			$cot_cache->page->clear('page');
		}

		$adminwarnings = $L['Deleted'];
	}
	elseif ($a == 'resyncall')
	{
		sed_check_xg();

		$adminwarnings = sed_structure_resyncall() ? $L['Resynced'] : $L['Error'];

		if ($cot_cache && $cfg['cache_page'])
		{
			$cot_cache->page->clear('page');
		}
	}

	$sql = sed_sql_query("SELECT DISTINCT(page_cat), COUNT(*) FROM $db_pages WHERE 1 GROUP BY page_cat");

	while ($row = sed_sql_fetcharray($sql))
	{
		$pagecount[$row['page_cat']] = $row['COUNT(*)'];
	}

	$totalitems = sed_sql_rowcount($db_structure);
	$pagenav = sed_pagenav('admin', 'm=structure', $d, $totalitems, $cfg['maxrowsperpage'], 'd', '', $cfg['jquery'] && $cfg['turnajax']);

	$sql = sed_sql_query("SELECT * FROM $db_structure ORDER BY structure_path ASC, structure_code ASC LIMIT $d, ".$cfg['maxrowsperpage']);

	$ii = 0;
	/* === Hook - Part1 : Set === */
	$extp = sed_getextplugins('admin.structure.loop');
	/* ===== */
	while ($row = sed_sql_fetcharray($sql))
	{
		$jj++;
		$structure_id = $row['structure_id'];
		$structure_code = $row['structure_code'];
		$structure_path = $row['structure_path'];
		$structure_title = $row['structure_title'];
		$structure_desc = $row['structure_desc'];
		$structure_icon = $row['structure_icon'];
		$structure_group = $row['structure_group'];
		$pathfieldlen = (mb_strpos($structure_path, '.') == 0) ? 3 : 9;
		$pathfieldimg = (mb_strpos($structure_path, '.') == 0) ? '' : '<img src="system/admin/img/join2.png" alt="" /> ';
		$pagecount[$structure_code] = (!$pagecount[$structure_code]) ? '0' : $pagecount[$structure_code];
		$raw = explode('.', $row['structure_order']);
		$sort = $raw[0];
		$way = $raw[1];

		reset($options_sort);
		reset($options_way);

		if (empty($row['structure_tpl']))
		{
			$structure_tpl_sym = '-';
		}
		elseif ($row['structure_tpl'] == 'same_as_parent')
		{
			$structure_tpl_sym = '*';
		}
		else
		{
			$structure_tpl_sym = '+';
		}

		$dozvil = ($pagecount[$structure_code] > 0) ? false : true;

		$t->assign(array(
			'ADMIN_STRUCTURE_UPDATE_DEL_URL' => sed_url('admin', 'm=structure&a=delete&id='.$structure_id.'&c='.$row['structure_code'].'&d='.$d.'&'.sed_xg()),
			'ADMIN_STRUCTURE_ID' => $structure_id,
			'ADMIN_STRUCTURE_CODE' => sed_inputbox('text', 's['.$structure_id.'][rcode]', $structure_code, 'size="8" maxlength="255"'),
			'ADMIN_STRUCTURE_PATHFIELDIMG' => $pathfieldimg,
			'ADMIN_STRUCTURE_PATH' => sed_inputbox('text', 's['.$structure_id.'][rpath]', $structure_path, 'size="'.$pathfieldlen.'" maxlength="24"'),
			'ADMIN_STRUCTURE_TPL_SYM' => $structure_tpl_sym,
			'ADMIN_STRUCTURE_TITLE' => sed_inputbox('text', 's['.$structure_id.'][rtitle]', $structure_title, 'size="24" maxlength="100"'),
			'ADMIN_STRUCTURE_GROUP' => sed_checkbox($structure_group, 's['.$structure_id.'][rgroup]'),
			'ADMIN_STRUCTURE_PAGECOUNT' => $pagecount[$structure_code],
			'ADMIN_STRUCTURE_JUMPTO_URL' => sed_url('list', 'c='.$structure_code),
			'ADMIN_STRUCTURE_RIGHTS_URL' => sed_url('admin', 'm=rightsbyitem&ic=page&io='.$structure_code),
			'ADMIN_STRUCTURE_OPTIONS_URL' => sed_url('admin', 'm=structure&n=options&id='.$structure_id.'&'.sed_xg()),
			'ADMIN_STRUCTURE_WAY' => sed_selectbox($way, 's['.$structure_id.'][rway]', array_keys($options_way), array_values($options_way), false, 'style="width:85px;"'),
			'ADMIN_STRUCTURE_ORDER' => sed_selectbox($sort, 's['.$structure_id.'][rorder]', array_keys($options_sort), array_values($options_sort), false, 'style="width:85px;"'),
			'ADMIN_STRUCTURE_ODDEVEN' => sed_build_oddeven($ii)
		));

		// Extra fields
		/* $extra_array = sed_build_extrafields('structure', 'ADMIN_STRUCTURE', $sed_extrafields['structure'], $row, false);
		$t->assign($extra_array);*/

		/* === Hook - Part2 : Include === */
		foreach ($extp as $pl)
		{
			include $pl;
		}
		/* ===== */

		$t->parse('MAIN.DEFULT.ROW');

		$ii++;
	}

	reset($options_sort);
	reset($options_way);

	$t->assign(array(
		'ADMIN_STRUCTURE_UPDATE_FORM_URL' => sed_url('admin', 'm=structure&a=update&d='.$d),
		'ADMIN_STRUCTURE_PAGINATION_PREV' => $pagenav['prev'],
		'ADMIN_STRUCTURE_PAGNAV' => $pagenav['main'],
		'ADMIN_STRUCTURE_PAGINATION_NEXT' => $pagenav['next'],
		'ADMIN_STRUCTURE_TOTALITEMS' => $totalitems,
		'ADMIN_STRUCTURE_COUNTER_ROW' => $ii,
		'ADMIN_PAGE_STRUCTURE_RESYNCALL' => sed_url('admin', 'm=structure&a=resyncall&'.sed_xg().'&d='.$d),
		'ADMIN_STRUCTURE_URL_FORM_ADD' => sed_url('admin', 'm=structure&a=add'),
		'ADMIN_STRUCTURE_CODE' => sed_inputbox('text', 'ncode', '', 'size="16"'),
		'ADMIN_STRUCTURE_PATH' => sed_inputbox('text', 'npath', '', 'size="16" maxlength="16"'),
		'ADMIN_STRUCTURE_TITLE' => sed_inputbox('text', 'ntitle', '', 'size="64" maxlength="100"'),
		'ADMIN_STRUCTURE_DESC' => sed_inputbox('text', 'ndesc', '', 'size="64" maxlength="255"'),
		'ADMIN_STRUCTURE_ICON' => sed_inputbox('text', 'nicon', '', 'size="64" maxlength="128"'),
		'ADMIN_STRUCTURE_GROUP' => sed_checkbox(0, 'ngroup'),
		'ADMIN_STRUCTURE_WAY' => sed_selectbox('asc', 'nway', array_keys($options_way), array_values($options_way), false),
		'ADMIN_STRUCTURE_ORDER' => sed_selectbox('title', 'norder', array_keys($options_sort), array_values($options_sort), false),
		'ADMIN_STRUCTURE_COMMENTS' => sed_radiobox(1, 'nallowcomments', array(1, 0), array($L['Yes'], $L['No'])),
		'ADMIN_STRUCTURE_RATINGS' => sed_radiobox(1, 'nallowratings', array(1, 0), array($L['Yes'], $L['No']))

	));

	// Extra fields
	foreach($sed_extrafields['structure'] as $i => $row2)
	{
		$uname = strtoupper($row['field_name']);
		$t->assign('ADMIN_STRUCTURE_'.$uname, sed_build_extrafields('structure',  $row2, '', true));
		$t->assign('ADMIN_STRUCTURE_'.$uname.'_TITLE', isset($L['structure_'.$row2['field_name'].'_title']) ?  $L['structure_'.$row2['field_name'].'_title'] : $row2['field_description']);

		// extra fields universal tags
		$t->assign('ADMIN_STRUCTURE_EXTRAFLD', sed_build_extrafields('structure',  $row2, '', true));
		$t->assign('ADMIN_STRUCTURE_EXTRAFLD_TITLE', isset($L['structure_'.$row2['field_name'].'_title']) ?  $L['structure_'.$row2['field_name'].'_title'] : $row2['field_description']);
		$t->parse('MAIN.DEFULT.EXTRAFLD');
	}

	$t->parse('MAIN.DEFULT');
}

$lincif_conf = sed_auth('admin', 'a', 'A');
$is_adminwarnings = isset($adminwarnings);

$t->assign(array(
	'ADMIN_STRUCTURE_ADMINWARNINGS' => $adminwarnings,
	'ADMIN_STRUCTURE_URL_CONFIG' => sed_url('admin', 'm=config&n=edit&o=core&p=structure'),
	'ADMIN_STRUCTURE_URL_EXTRAFIELDS' => sed_url('admin', 'm=extrafields&n=structure')
));

/* === Hook  === */
$extp = sed_getextplugins('admin.structure.tags');
foreach ($extp as $pl)
{
	include $pl;
}
/* ===== */

$t->parse('MAIN');
if (SED_AJAX)
{
	$t->out('MAIN');
}
else
{
	$adminmain = $t->text('MAIN');
}

?>