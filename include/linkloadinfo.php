<?php
/**
 * Module: WF-Links
 * Version: v1.0.3
 * Release Date: 21 June 2005
 * Developer: John N
 * Team: WF-Projects
 * Licence: GNU
 */

use XoopsModules\Wflinks;

$module_link = '';

/** @var Wflinks\Helper $helper */
$helper = Wflinks\Helper::getInstance();

$link['id']        = (int)$link_arr['lid'];
$link['cid']       = (int)$link_arr['cid'];
$link['published'] = (int)$link_arr['published'] ? true : false;

$path             = $mytree->getPathFromId($link_arr['cid'], 'title');
$path             = mb_substr($path, 1);
$path             = basename($path);
$path             = str_replace('/', '', $path);
$link['category'] = $path;

$rating          = round(number_format($link_arr['rating'], 0) / 2);
$link['rateimg'] = 'rate' . $rating . '.gif';
unset($rating);

$link['votes'] = (1 == $link_arr['votes']) ? _MD_WFL_ONEVOTE : sprintf(_MD_WFL_NUMVOTES, $link_arr['votes']);
$link['hits']  = sprintf(_MD_WFL_LINKHITS, (int)$link_arr['hits']);
$xoopsTpl->assign('lang_dltimes', $link['hits']);

$link['title'] = $link_arr['title'];
$link['url']   = $link_arr['url'];

// Get Google Pagerank
if (null !== $helper->getConfig('showpagerank') && 1 == $helper->getConfig('showpagerank')) {
    $link['pagerank'] = Wflinks\Utility::pagerank($link['url']);
}

if (isset($link_arr['screenshot'])) {
    $link['screenshot_full'] = htmlspecialchars($link_arr['screenshot']);
    if (!empty($link_arr['screenshot'])
        && file_exists(XOOPS_ROOT_PATH . '/' . $helper->getConfig('screenshots') . '/' . xoops_trim($link_arr['screenshot']))) {
        if (null !== $helper->getConfig('usethumbs') && 1 == $helper->getConfig('usethumbs')) {
            $_thumb_image = new Wflinks\ThumbsNails($link['screenshot_full'], $helper->getConfig('screenshots'), 'thumbs');
            if ($_thumb_image) {
                $_thumb_image->setUseThumbs(1);
                $_thumb_image->setImageType('gd2');
                $_image = $_thumb_image->createThumb($helper->getConfig('shotwidth'), $helper->getConfig('shotheight'), $helper->getConfig('imagequality'), $helper->getConfig('updatethumbs'), $helper->getConfig('keepaspect'));
            }
            $link['screenshot_thumb'] = XOOPS_URL . "/{$helper->getConfig('screenshots')}/$_image";
        } else {
            $link['screenshot_thumb'] = XOOPS_URL . "/{$helper->getConfig('screenshots')}/" . xoops_trim($link_arr['screenshot']);
        }
    }
}

if (0 == $moderate) {
    $time       = (0 != $link_arr['updated']) ? $link_arr['updated'] : $link_arr['published'];
    $is_updated = (0 != $link_arr['updated']) ? _MD_WFL_UPDATEDON : _MD_WFL_PUBLISHDATE;
    $xoopsTpl->assign('lang_subdate', $is_updated);
} else {
    $time       = $link_arr['date'];
    $is_updated = _MD_WFL_SUBMITDATE;
    $xoopsTpl->assign('lang_subdate', $is_updated);
}

$link['updated'] = formatTimestamp($time, $helper->getConfig('dateformat'));
$description     = $myts->displayTarea($link_arr['description'], 1, 1, 1, 1, 1);

$link['description'] = xoops_substr($description, 0, $helper->getConfig('totalchars'), '...');
xoops_load('XoopsUserUtility');
$link['submitter'] = \XoopsUserUtility::getUnameFromId($link_arr['submitter']);
$link['publisher'] = (isset($link_arr['publisher'])
                      && !empty($link_arr['publisher'])) ? htmlspecialchars($link_arr['publisher']) : _MD_WFL_NOTSPECIFIED;

$country             = $link_arr['country'];
$link['country']     = XOOPS_URL . '/' . $helper->getConfig('flagimage') . '/' . $country . '.gif';
$link['countryname'] = Wflinks\Utility::getCountryName($link_arr['country']);

$mail_subject     = rawurlencode(sprintf(_MD_WFL_INTFILEFOUND, $xoopsConfig['sitename']));
$mail_body        = rawurlencode(sprintf(_MD_WFL_INTFILEFOUND, $xoopsConfig['sitename']) . ':  ' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/singlelink.php?cid=' . $link_arr['cid'] . '&amp;lid=' . $link_arr['lid']);
$link['isadmin']  = ((is_object($xoopsUser) && !empty($xoopsUser))
                     && $xoopsUser->isAdmin($xoopsModule->mid()));
$link['comments'] = $link_arr['comments'];
$whoisurl         = str_replace('http://', '', $link['url']);

$link['adminlink'] = '';
if (0 == $moderate && true === $link['isadmin']) {
    $link['adminlink'] = '<a href="'
                         . XOOPS_URL
                         . '/modules/'
                         . $xoopsModule->getVar('dirname')
                         . '/admin/index.php"><img src="'
                         . XOOPS_URL
                         . '/modules/'
                         . $xoopsModule->getVar('dirname')
                         . '/assets/images/icon/computer.png" alt="'
                         . _MD_WFL_ADMINSECTION
                         . '" title="'
                         . _MD_WFL_ADMINSECTION
                         . '" align="absmiddle"></a>&nbsp;';
    $link['adminlink'] .= '<a href="'
                          . XOOPS_URL
                          . '/modules/'
                          . $xoopsModule->getVar('dirname')
                          . '/admin/main.php?op=edit&amp;lid='
                          . $link_arr['lid']
                          . '"><img src="'
                          . \Xmf\Module\Admin::iconUrl('', 16)
                          . '/edit.png" alt="'
                          . _MD_WFL_EDIT
                          . '" title="'
                          . _MD_WFL_EDIT
                          . '" align="absmiddle"></a>&nbsp;';
    $link['adminlink'] .= '<a href="'
                          . XOOPS_URL
                          . '/modules/'
                          . $xoopsModule->getVar('dirname')
                          . '/admin/main.php?op=delete&amp;lid='
                          . $link_arr['lid']
                          . '"><img src="'
                          . \Xmf\Module\Admin::iconUrl('', 16)
                          . '/delete.png" alt="'
                          . _MD_WFL_DELETE
                          . '" title="'
                          . _MD_WFL_DELETE
                          . '" align="absmiddle"></a>&nbsp;';
    $link['adminlink'] .= '<a href="http://whois.domaintools.com/' . $whoisurl . '" target="_blank"><img src="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/assets/images/icon/domaintools.png" alt="WHOIS" title="WHOIS" align="absmiddle"></a>';
} else {
    $link['adminlink'] = '[ <a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/submit.php?op=edit&amp;lid=' . $link_arr['lid'] . '&approve=1">' . _MD_WFL_APPROVE . '</a> | ';
    $link['adminlink'] .= '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/submit.php?op=delete&amp;lid=' . $link_arr['lid'] . '">' . _MD_WFL_DELETE . '</a> ]';
}

$votestring = (1 == $link_arr['votes']) ? _MD_WFL_ONEVOTE : sprintf(_MD_WFL_NUMVOTES, $link_arr['votes']);

$link['useradminlink'] = 0;
if (is_object($xoopsUser) && !empty($xoopsUser)) {
    $_user_submitter = $xoopsUser->getVar('uid') == $link_arr['submitter'];
    if (true === Wflinks\Utility::checkGroups($cid)) {
        $link['useradminlink'] = 1;
        if ($xoopsUser->getVar('uid') == $link_arr['submitter']) {
            $link['usermodify'] = '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/submit.php?lid=' . $link_arr['lid'] . '"> ' . _MD_WFL_MODIFY . '</a> |';
        }
    }
}

switch ($helper->getConfig('selectforum')) {
    case '1':
        $forum             = 'newbb';
        $forum_path_prefix = '/modules/newbb/viewforum.php?forum=';
        break;
    case '2':
        $forum             = 'ipboard';
        $forum_path_prefix = '/modules/ipboard/index.php?showforum=';
        break;
    case '3':
        $forum             = 'pbboard';
        $forum_path_prefix = '/modules/pbboard/viewforum.php?f=';
        break;
    case '4':
        $forum             = 'newbbex';
        $forum_path_prefix = '/modules/newbbex/viewforum.php?forum=';
        break;
}
$xoopsforumModule = $xoopsModule::getByDirname($forum);
if (is_object($xoopsforumModule) && $xoopsforumModule->getVar('isactive')) {
    $link['forumid']    = ($link_arr['forumid'] > 0) ? $link_arr['forumid'] : 0;
    $link['forum_path'] = $forum_path_prefix . (string)$link['forumid'];
}

$xoopsTpl->assign('ratethislink', '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/ratelink.php?cid=' . $link_arr['cid'] . '&amp;lid=' . $link_arr['lid'] . '">' . _MD_WFL_RATETHISFILE . '</a>');

$xoopsTpl->assign('reportbroken', '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/brokenlink.php?lid=' . $link_arr['lid'] . '">' . _MD_WFL_REPORTBROKEN . '</a>');

$xoopsTpl->assign('mailto', '<a href="mailto:?subject=' . $mail_subject . '&body=' . $mail_body . '" target="_top">' . _MD_WFL_TELLAFRIEND . '</a>');

$xoopsTpl->assign('commentz', '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/singlelink.php?cid=' . $link_arr['cid'] . '&amp;lid=' . $link_arr['lid'] . '">' . _COMMENTS . '&nbsp;(' . $link_arr['comments'] . ')</a>');

$xoopsTpl->assign('print', '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/print.php?lid=' . $link_arr['lid'] . '"  target="_blank">' . _MD_WFL_PRINT . '</a>');

$link['icons']         = Wflinks\Utility::displayIcons($link_arr['published'], $link_arr['status'], $link_arr['hits']);
$link['allow_rating']  = Wflinks\Utility::checkGroups($cid, 'WFLinkRatePerms') ? true : false;
$link['total_chars']   = $helper->getConfig('totalchars');
$link['module_dir']    = $xoopsModule->getVar('dirname');
$link['otherlinx']     = $helper->getConfig('otherlinks');
$link['showpagerank']  = $helper->getConfig('showpagerank');
$link['quickview']     = $helper->getConfig('quickview');
$link['comment_rules'] = $helper->getConfig('com_rule');
$link['autoscrshot']   = $helper->getConfig('useautothumb');
