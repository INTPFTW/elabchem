<?php
/********************************************************************************
*                                                                               *
*   Copyright 2012 Nicolas CARPi (nicolas.carpi@gmail.com)                      *
*   http://www.elabftw.net/                                                     *
*                                                                               *
********************************************************************************/

/********************************************************************************
*  This file is part of eLabFTW.                                                *
*                                                                               *
*    eLabFTW is free software: you can redistribute it and/or modify            *
*    it under the terms of the GNU Affero General Public License as             *
*    published by the Free Software Foundation, either version 3 of             *
*    the License, or (at your option) any later version.                        *
*                                                                               *
*    eLabFTW is distributed in the hope that it will be useful,                 *
*    but WITHOUT ANY WARRANTY; without even the implied                         *
*    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR                    *
*    PURPOSE.  See the GNU Affero General Public License for more details.      *
*                                                                               *
*    You should have received a copy of the GNU Affero General Public           *
*    License along with eLabFTW.  If not, see <http://www.gnu.org/licenses/>.   *
*                                                                               *
********************************************************************************/
if(isset($_SESSION['prefs']['theme'])) {
require_once("themes/".$_SESSION['prefs']['theme']."/highlight.css");
}
?>

<script type="text/javascript">
	$("head").append('<meta http-equiv="X-UA-Compatible" content="chrome=1">',
				'<link rel="stylesheet" href="js/chemdoodleweb/ChemDoodleWeb.css" type="text/css">',
				'<script type="text/javascript" src="js/chemdoodleweb/ChemDoodleWeb-libs.js"/>',
				'<script type="text/javascript" src="js/chemdoodleweb/ChemDoodleWeb.js"/>',
				'<script type="text/javascript" src="js/schemeViewer.js"/>');
</script>


<div id='submenu'>
    <form id='big_search' method='get' action='experiments.php'>
        <input id='big_search_input' type='search' name='q' size='50' placeholder='Type your search' />
    </form>
    <br />
    <a href="create_item.php?type=exp"><img src="themes/<?php echo $_SESSION['prefs']['theme'];?>/img/create.gif" alt="" /> Create experiment</a> | 
    <a href='#' class='trigger'><img src="themes/<?php echo $_SESSION['prefs']['theme'];?>/img/duplicate.png" alt="" /> Create from template</a> |
    <a onmouseover="changeSrc('<?php echo $_SESSION['prefs']['theme'];?>')" onmouseout="stopAnim('<?php echo $_SESSION['prefs']['theme'];?>')" href='experiments.php?mode=show&q=runningonly'><img id='runningimg' src="themes/<?php echo $_SESSION['prefs']['theme'];?>/img/running.fix.png" alt="running" /> Show running experiments</a>
</div><!-- end submenu -->
<div class='toggle_container'><ul>
<?php // SQL to get user's templates
$sql = "SELECT id, name FROM experiments_templates WHERE userid = :userid";
$tplreq = $bdd->prepare($sql);
$tplreq->execute(array(
    'userid' => $_SESSION['userid']
));
$count_tpl = $tplreq->rowCount();
if ($count_tpl > 0) {
    while ($tpl = $tplreq->fetch()) {
        echo "<li class='inline'><a href='create_item.php?type=exp&tpl=".$tpl['id']."' class='templates'>".$tpl['name']."</a></li> ";
    }
} else { // user has no templates
    echo "You do not have any templates yet. Go to <a href='ucp.php'>your control panel</a> to make one !";
}
?>
</ul></div><br />
<?php
// VIEWING PREFS //
$display = $_SESSION['prefs']['display'];
$order = $_SESSION['prefs']['order'];
$sort = $_SESSION['prefs']['sort'];
$limit = $_SESSION['prefs']['limit'];

// OFFSET
if ((!isset($_GET['offset'])) || (empty($_GET['offset']))) {
    $offset = '0';
} elseif (is_pos_int($_GET['offset'])){
    $offset = $_GET['offset'];
} else {
    die("<p>Bad offset value</p>");
}

// Check CURRENTPAGE
if ((!isset($_GET['currentpage'])) || (empty($_GET['currentpage']))) {
    // $currentpage must start at 0 to have $offset = 0
    $currentpage = '0';
} elseif ((is_pos_int($_GET['currentpage']) && ($_GET['currentpage'] > 0))){
    $currentpage = $_GET['currentpage'];
} else {
    $currentpage = 0;
}
// for pagination
$offset = $currentpage * $limit;

// SQL for showXP
// reminder : order by and sort must be passed to the prepare(), not during execute()
// /////////////////
// SEARCH
// /////////////////
if (isset($_GET['q'])) { // if there is a query
    $query = filter_var($_GET['q'], FILTER_SANITIZE_STRING);

    // RUNNING ONLY

    if ($query === 'runningonly') {
        $results_arr = array();
        // show only running XP
        $sql = "SELECT id FROM experiments 
        WHERE userid_creator = :userid AND status = 'running' LIMIT 100";
        $req = $bdd->prepare($sql);
        $req->execute(array(
            'userid' => $_SESSION['userid']
        ));
        // put resulting ids in the results array
        while ($data = $req->fetch()) {
            $results_arr[] = $data['id'];
        }

    } else {
        // normal search
        $results_arr = search_item('xp', $query, $_SESSION['userid']);
    }

    // show number of results found
    if (count($results_arr) > 1){
        echo "Found ".count($results_arr)." results.";
    } elseif (count($results_arr) == 1){
        echo "Found 1 result.";
    } else {
        echo "No experiments were found.";
    }

    // loop the results array and display results
    foreach($results_arr as $result_id) {
        showXP($result_id, $display);
    } // end foreach
///////// END SEARCH


// /////////////
// RELATED
// /////////////
} elseif (isset($_GET['related']) && is_pos_int($_GET['related'])) {// search for related experiments to DB item id
    $item_id = $_GET['related'];
    // we make an array for the resulting ids
    $results_arr = array();
    // search in title date and body
    $sql = "SELECT item_id FROM experiments_links 
        WHERE link_id = :link_id LIMIT 100";
    $req = $bdd->prepare($sql);
    $req->execute(array(
        'link_id' => $item_id
    ));
    // put resulting ids in the results array
    while ($data = $req->fetch()) {
        $results_arr[] = $data['item_id'];
    }
    $req->closeCursor();
    // show number of results found
    if (count($results_arr) > 1){
        echo "Found ".count($results_arr)." results.";
    } elseif (count($results_arr) == 1){
        echo "Found 1 result.";
    } else {
        echo "<p>No experiments are linked with this item.</p>";
    }

    // loop the results array and display results
    foreach($results_arr as $result_id) {
        showXP($result_id, $display);
    } // end foreach

///////////////
// TAG SEARCH
///////////////
} elseif (isset($_GET['tag']) && !empty($_GET['tag'])) {
    $tag = filter_var($_GET['tag'], FILTER_SANITIZE_STRING);
        $results_arr = array();
        $sql = "SELECT item_id FROM experiments_tags
        WHERE tag LIKE :tag";
        $req = $bdd->prepare($sql);
        $req->execute(array(
            'tag' => $tag
        ));
        // put resulting ids in the results array
        while ($data = $req->fetch()) {
            $results_arr[] = $data['item_id'];
        }

    // show number of results found
    if (count($results_arr) > 1){
        echo "Found ".count($results_arr)." results.";
    } elseif (count($results_arr) == 1){
        echo "Found 1 result.";
    } else {
        echo "No experiments were found.";
    }

    // clean duplicates
    $results_arr = array_unique($results_arr);
    // loop the results array and display results
    foreach($results_arr as $result_id) {
        showXP($result_id, $display);
    } // end foreach

// /////////////////
// DEFAULT VIEW
// /////////////////
} else {
    $sql = "SELECT * 
        FROM experiments 
        WHERE userid_creator = :userid 
        AND status <> 'deleted'
        ORDER BY :order :sort 
        LIMIT :limit
        OFFSET :offset";
    $req = $bdd->prepare($sql);
	$req->bindValue('limit', (int) $limit, PDO::PARAM_INT);
	$req->bindValue('offset', (int) $offset, PDO::PARAM_INT);
	$req->bindValue('userid', $_SESSION['userid'], PDO::PARAM_INT);
	$req->bindValue('order', $order, PDO::PARAM_STR);
	$req->bindValue('sort', $sort, PDO::PARAM_STR);
    $req->execute();
    $count = $req->rowCount();
    if($count == 0) {
        echo '<p>Welcome to elabftw :)<br />
            Click the «Create experiment» button to get started.</p>';
    } else {
        while ($data = $req->fetch()) {
            $results_arr[] = $data['id'];
        }
        $req->closeCursor();
        // loop the results array and display results
        foreach($results_arr as $result_id) {
            showXP($result_id, $display);
        } // end foreach
    }
} // END CONTENT

// PAGINATION
//  only show pagination if there is no search
if (isset($_GET['q']) || isset($_GET['related'])) {
    // no pagination
    // inverted because otherwise it doesn't work
} else {
    ?>
    <section class='pagination'>
    <?php
    // COUNT TOTAL NUMBER OF ITEMS
    $sql = "SELECT COUNT(id) FROM experiments WHERE userid_creator = :userid AND status <> 'deleted'";
    $req = $bdd->prepare($sql);
    $req->execute( array('userid' => $_SESSION['userid']));
    $full = $req->fetchAll();
    $numrows = $full[0][0];

    // find out total pages
    $totalpages = (ceil($numrows / $limit) - 1);
    // if current page is greater than total pages...
    if ($currentpage > $totalpages) {
       // set current page to last page
       $currentpage = $totalpages;
    } // end if
    // if current page is less than first page...
    if ($currentpage < 0) {
       // set current page to first page
       $currentpage = 0;
    } // end if

    /******  build the pagination links ******/
    // range of num links to show
    $range = 3;

    // if not on page 0, show back links
    if ($currentpage != 0) {
       // show << link to go back to page 1
       echo " <a href='".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&currentpage=0'><<</a> ";
       // get previous page num
       $prevpage = $currentpage - 1;
       // show < link to go back to 1 page
       echo " <a href='".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&currentpage=$prevpage'><</a> ";
    } // end if 

    // loop to show links to range of pages around current page
    for ($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++) {
       // if it's a valid page number...
       if (($x >= 0) && ($x <= $totalpages)) {
          // if we're on current page...
          if ($x == $currentpage) {
             // 'highlight' it but don't make a link
             echo " [<b>$x</b>] ";
          // if not current page...
          } else {
             // make it a link
         echo " <a href='".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&currentpage=$x'>$x</a> ";
          } // end else
       } // end if 
    } // end for
             
    // if not on last page, show forward and last page links	
    if ($currentpage != $totalpages) {
       // get next page
       $nextpage = $currentpage + 1;
        // echo forward link for next page 
       echo " <a href='".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&currentpage=$nextpage'>></a> ";
       // echo forward link for lastpage
       echo " <a href='".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&currentpage=$totalpages'>>></a> ";
    } // end if
    /****** end build pagination links ******/
    ?>
    </section>
<?php
} // end if there is no search
?>
<script>
<?php
// KEYBOARD SHORTCUTS
echo "key('".$_SESSION['prefs']['shortcuts']['create']."', function(){location.href = 'create_item.php?type=exp'});";
?>
// TOGGLE DIV
$(document).ready(function(){
	$(".toggle_container").hide();
	$("a.trigger").click(function(){
		$('div.toggle_container').slideToggle(1);
	});
});
// ANIMATE RUNNING ICON
function changeSrc(theme){
    document.getElementById('runningimg').src = 'themes/'+theme+'/img/running.png';
}
function stopAnim(theme){
    document.getElementById('runningimg').src = 'themes/'+theme+'/img/running.fix.png';
}
</script>
