<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=users.register.add.first
[END_COT_EXT]
==================== */

/**
 * Banlist
 *
 * @package Banlist
 * @version 0.9.0
 * @author Cotonti Team
 * @copyright Copyright (c) Cotonti Team 2008-2010
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL');
cot_require_lang('banlist', 'plug');

$GLOBALS['db_banlist'] = (isset($GLOBALS['db_banlist'])) ? $GLOBALS['db_banlist'] : $GLOBALS['db_x'] . 'banlist';
$ruser['user_email'] = cot_import('ruseremail','P','TXT',64, TRUE);
$ruser['user_email'] = mb_strtolower($ruser['user_email']);

$sql = $db->query("SELECT banlist_reason, banlist_email FROM $db_banlist WHERE banlist_email LIKE'%".$ruser['user_email']."%'");
if ($row = $sql->fetch())
{
		cot_error($L['aut_emailbanned'].$bannedreason);
}


?>