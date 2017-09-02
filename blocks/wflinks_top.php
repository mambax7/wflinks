<?php
/**
 *
 * Module: WF-links
 * Version: v1.0.3
 * Release Date: 21 June 2005
 * Developer: John N
 * Team: WF-Projects
 * Licence: GNU
 */

// defined('XOOPS_ROOT_PATH') || exit('Restricted access.');

// checkBlockgroups()
//
// @param integer $cid
// @param string $permType
// @param boolean $redirect
// @return
/**
 * @param int    $cid
 * @param string $permType
 * @param bool   $redirect
 *
 * @return bool
 */
function checkBlockgroups($cid = 0, $permType = 'WFLinkCatPerm', $redirect = false)
{
    $moduleDirName = basename(dirname(__DIR__));
    global $xoopsUser;

    $groups       = is_object($xoopsUser) ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
    $gpermHandler = xoops_getHandler('groupperm');

    /** @var XoopsModuleHandler $moduleHandler */
    $moduleHandler = xoops_getHandler('module');
    $module        = $moduleHandler->getByDirname($moduleDirName);

    if (!$gpermHandler->checkRight($permType, $cid, $groups, $module->getVar('mid'))) {
        if ($redirect === false) {
            return false;
        } else {
            redirect_header('index.php', 3, _NOPERM);
        }
    }
    unset($module);

    return true;
}

// Function: b_mylinks_top_show
// Input   : $options[0] = date for the most recent links
//                 hits for the most popular links
//           $options[1] = How many links are displayes
//           $options[2] = Length of title
//           $options[3] = Date format
//           $block['content'] = The optional above content
// Output  : Returns the most recent or most popular links
/**
 * @param $options
 *
 * @return array
 */
function b_wflinks_top_show($options)
{
    $moduleDirName = basename(dirname(__DIR__));
    global $xoopsDB;

    $block           = array();
    $time            = time();
    $moduleHandler   = xoops_getHandler('module');
    $wflModule       = $moduleHandler->getByDirname($moduleDirName);
    $configHandler   = xoops_getHandler('config');
    $wflModuleConfig = $configHandler->getConfigsByCat(0, $wflModule->getVar('mid'));
    $wfmyts          = MyTextSanitizer:: getInstance();

    $result = $xoopsDB->query('SELECT lid, cid, title, published, hits FROM ' . $xoopsDB->prefix('wflinks_links') . ' WHERE published > 0 AND published <= ' . $time . ' AND (expired = 0 OR expired > ' . $time . ') AND offline = 0 ORDER BY ' . $options[0] . ' DESC', $options[1], 0);
    while ($myrow = $xoopsDB->fetchArray($result)) {
        if (false == checkBlockgroups($myrow['cid']) || $myrow['cid'] == 0) {
            continue;
        }
        $linkload = array();
        $title    = $wfmyts->htmlSpecialChars($wfmyts->stripSlashesGPC($myrow['title']));
        if (!XOOPS_USE_MULTIBYTES) {
            if (strlen($myrow['title']) >= $options[2]) {
                $title = substr($myrow['title'], 0, $options[2] - 1) . '...';
            }
        }
        $linkload['id']    = (int)$myrow['lid'];
        $linkload['cid']   = (int)$myrow['cid'];
        $linkload['title'] = $title;
        if ($options[0] === 'published') {
            $linkload['date'] = formatTimestamp($myrow['published'], $options[3]);
        } elseif ($options[0] === 'hits') {
            $linkload['hits'] = $myrow['hits'];
        }
        $linkload['dirname'] = $wflModule->getVar('dirname');
        $block['links'][]    = $linkload;
    }
    unset($_block_check_array);

    return $block;
}

// b_wflinks_top_edit()
//
// @param $options
// @return
/**
 * @param $options
 *
 * @return string
 */
function b_wflinks_top_edit($options)
{
    $form = '' . _MB_WFL_DISP . '&nbsp;';
    $form .= "<input type='hidden' name='options[]' value='";
    if ($options[0] === 'published') {
        $form .= "published'";
    } else {
        $form .= "hits'";
    }
    $form .= '>';
    $form .= "<input type='text' name='options[]' value='" . $options[1] . "'>&nbsp;" . _MB_WFL_FILES . '';
    $form .= '&nbsp;<br>' . _MB_WFL_CHARS . "&nbsp;<input type='text' name='options[]' value='" . $options[2] . "'>&nbsp;" . _MB_WFL_LENGTH . '';
    $form .= '&nbsp;<br>' . _MB_WFL_DATEFORMAT . "&nbsp;<input type='text' name='options[]' value='" . $options[3] . "'>&nbsp;" . _MB_WFL_DATEFORMATMANUAL;

    return $form;
}