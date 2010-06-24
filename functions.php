<?php

function icke_getFile($name) {
    return file_exists(DOKU_TPLINC . 'local/' . $name) ?
           'local/' . $name : $name;
}

function icke_tplPopupPage($id){
    $page = p_wiki_xhtml($id,'',false);
    if($page) {
        echo '<div class="sec_level">';
        echo $page;
        echo '<div class="sec_level_bottom"></div>';
        echo '</div>';
    }
}

function icke_tplProjectSteps(){
    global $ID;
    global $conf;

    $steps = '';
    $ns = $ID;
    do {
        $ns = getNS($ns);
        $try = $ns . ':schritt';
        if(page_exists($try)) {
            $steps = $try;
            break;
        }
        $try .= ':' . $conf['start'];
        if(page_exists($try)) {
            $steps = $try;
            break;
        }
    } while ($ns);

    if (!$steps) return;

    echo '<li class="sideclip">';
    echo p_wiki_xhtml($steps,'',false);
    echo '</li>';
}

function icke_tplSidebar() {
    global $ID;
    include DOKU_TPLINC . icke_getFile('namespaces.php');
    if (isset($_SERVER['REMOTE_USER'])) {
        $firstkey = reset(array_keys($icke_namespaces));
        $icke_namespaces = array_merge(array('dashboard' => array
                                                ('txt' => 'Dashboard',
                                                 'id' => tpl_getConf('user_ns') .
                                          $_SERVER['REMOTE_USER'] .
                                          ':dashboard')),
                                    $icke_namespaces);
        $icke_namespaces[$firstkey]['liclass'] = 'separator';
    }

    $hasactive = false;

    foreach ($icke_namespaces as $class => $data) {
        if (!isset($data['id'])) {
            $data['id'] = $class . ':';
        }
        if (!$hasactive && strpos($ID, $data['id']) === 0) {
            $data['liclass'] .= ' active';
            $hasactive = true;
        }
        if (auth_quickaclcheck($data['id']) < AUTH_READ) {
            continue;
        }

        echo '<li' . (isset($data['liclass']) ? ' class="'.$data['liclass'].'"' : '') .
             '><a class="' . $class . '" href="' . wl($data['id']) . '">' . $data['txt'] . '</a>';
        icke_tplPopupPage($data['id'] . (strpos($data['id'], ':') !== false ? 'quick' : '_quick'));
        echo '</li>';
    }
    ?>
        <li class="separator"><a class="einstellungen">Einstellungen</a>
            <div class="sec_level">
                <h1 class="empty"></h1>
                <ul>
<?php
                include DOKU_TPLINC . icke_getFile('tools.php');
                foreach($icke_tools as $tool) {
                    switch ($tool['type']) {
                    case 'action':
                        $str = tpl_actionlink($tool['value'], '', '', '', true);
                        break;
                    case 'string':
                        $str = $tool['value'];
                        break;
                    default:
                        $str = call_user_func_array($tool['func'], $tool['value']);
                    }
                    if ($str !== '') {
                        echo '<li>' . $str . '</li>';
                    }
                }
?>
                </ul>
                <div class="sec_level_bottom"></div>
            </div>

        </li>
    <?php
}

function icke_tplSearch() {
    include DOKU_TPLINC . icke_getFile('namespaces.php');
    foreach ($icke_namespaces as $id => &$ns) {
        $ns['img'] = DOKU_TPL . 'local/images/icons/30x30/' . $id . '_aktiv.png';
    }
    $fancysearch = plugin_load('action', 'fancysearch');
    if (!is_null($fancysearch)) {
        $fancysearch->tpl_searchform($icke_namespaces, DOKU_TPL . 'images/icons/30x30/icke.png');
    }
}

function icke_tplMenuCSS() {
    include DOKU_TPLINC . icke_getFile('namespaces.php');
    echo '<style type="text/css">';
    $nss = array_keys($icke_namespaces);
    $nss[] = 'dashboard';
    $nss[] = 'einstellungen';
    $str = '';
    foreach($nss as $ns) {
         $str .= '#icke__quicknav a.' . $ns . ",\n";
    }
    echo rtrim($str, ",\n");
    ?>
    {
        background: transparent top left no-repeat;
        display: block;
        height: 60px;
        text-indent: -9999px;
        width: 60px;
    }
    <?php
    foreach($nss as $ns) {
        echo "#icke__quicknav a.$ns {background-image: url(" . DOKU_TPL .
             icke_getFile('images/icons/60x60/' . $ns . '_inaktiv.png') . ");}
              #icke__quicknav li.active a.$ns,
              #icke__quicknav li:hover a.$ns {background-image: url(" . DOKU_TPL .
             icke_getFile('images/icons/60x60/' . $ns . '_aktiv.png') . ");}";
    }
    echo '</style>';
}

function icke_tplFavicon() {
    echo '  <link rel="shortcut icon" href="' . DOKU_TPL . icke_getFile('images/favicon.png') . '" />';
}

function icke_startupCheck() {
    $plugins = plugin_list();
    $requiredPlugins = array('jquery');
    foreach ($requiredPlugins as $req) {
        if (in_array($req, $plugins)) continue;
        msg('ICKE-template requires the '. hsc($req).'-plugin.');
    }
}
