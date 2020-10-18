<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.xoStats.php
 * Type:     function
 * Name:     xoStats
 * Purpose:  XOOPS Members Statistics
 * Examples:
 	    <{xoStats}>
		Latest Member : <a href="<{$xoops_url}>/userinfo.php?uid=<{$latestuid}>"><{$latestmemberuname}></a><br>
		Total Post 	 : <{$totalpost}><br>
		Total User   : <{$totaluser}><br>
		Total Online : <{$totalonline}><br>
		Registered Today     : <{$totalregisteredtoday}><br>
		Registered Yesterday : <{$totalregisteredyesterday}><br>
 * -------------------------------------------------------------
 */
function smarty_function_xoStats($params, &$smarty)
{
		global $xoopsConfig, $xoopsUser, $xoopsModule, $xoopsDB, $_SERVER;	
		$memberHandler      = xoops_getHandler('member');
        $today              = formatTimestamp(time());
	
	// Gettting Total Online Users
	/* @var XoopsOnlineHandler $online_handler */
    $online_handler = xoops_getHandler('online');
    // set gc probabillity to 10% for now..
    if (mt_rand(1, 100) < 11) {
        $online_handler->gc(300);
    }
    if (is_object($xoopsUser)) {
        $uid   = $xoopsUser->getVar('uid');
        $uname = $xoopsUser->getVar('uname');
    } else {
        $uid   = 0;
        $uname = '';
    }
    $requestIp = \Xmf\IPAddress::fromRequest()->asReadable();
    $requestIp = (false === $requestIp) ? '0.0.0.0' : $requestIp;
    if (is_object($xoopsModule)) {
        $online_handler->write($uid, $uname, time(), $xoopsModule->getVar('mid'), $requestIp);
    } else {
        $online_handler->write($uid, $uname, time(), 0, $requestIp);
    }
    $onlines = $online_handler->getAll();
    if (!empty($onlines)) {
        $totalonline   = count($onlines);
		$smarty->assign('totalonline',$totalonline);
        }
		
		//Getting Total Registered Users
        $level_criteria     = new \Criteria('level', 0, '>');
        $criteria           = new \CriteriaCompo($level_criteria);
        $criteria24         = new \CriteriaCompo($level_criteria);
        $criteria48         = new \CriteriaCompo($level_criteria);
        $totaluser		    = $memberHandler->getUserCount($level_criteria);
        $smarty->assign('totaluser',$totaluser);
		
		
		//Getting User Registration Statistics
        $users_reg_24 = $memberHandler->getUserCount($criteria24->add(new \Criteria('user_regdate', (mktime(0, 0, 0) - (24 * 3600)), '>=')), 'AND');
        $users_reg_48 = $memberHandler->getUserCount($criteria48->add(new \Criteria('user_regdate', (mktime(0, 0, 0) - (48 * 3600)), '>=')), 'AND');
        $todayregister       = $users_reg_24;
        $yesterdayregister   = $users_reg_48;
       	$smarty->assign('totalregisteredtoday',$todayregister);
		$smarty->assign('totalregisteredyesterday',$yesterdayregister);
		
        // Getting Last Registered Member
		$limit        = 1;
        $criteria->setOrder('DESC');
        $criteria->setSort('user_regdate');
        $criteria->setLimit($limit);
        $lastmembers  = $memberHandler->getUsers($criteria);
        $lastusername = $lastmembers[0]->getVar('uname');
        $lastrealname = $lastmembers[0]->getVar('name');
        $lastuid       = $lastmembers[0]->getVar('uid');
        $latestmembername = $lastrealname;
		$latestmemberuname = $lastusername;
        $latestuid = $lastuid;
		$smarty->assign('latestmemembername',$latestmembername);
		$smarty->assign('latestmemberuname',$latestmemberuname);
		$smarty->assign('latestuid',$lastuid);
		
        //Total Post Count
        $sql                   = 'SELECT SUM(posts) AS totalpost FROM ' . $GLOBALS['xoopsDB']->prefix('users') . ' WHERE level > 0';
        $result                = $GLOBALS['xoopsDB']->query($sql);
        $myrow				   = $GLOBALS['xoopsDB']->fetchArray($result);
        $totalpost			   = $myrow['totalpost'];
		$smarty->assign('totalpost',$totalpost);
		
}
?>
