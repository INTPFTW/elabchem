<?php
/********************************************************************************
*  This file is part of eLabChem (http://github.com/martinp23/elabchem)         *
*  Copyright (c) 2013 Martin Peeks (martinp23@googlemail.com)                   *
*                                                                               *
*    eLabChem is free software: you can redistribute it and/or modify           *
*    it under the terms of the GNU Affero General Public License as             *
*    published by the Free Software Foundation, either version 3 of             *
*    the License, or (at your option) any later version.                        *
*                                                                               *
*    eLabChem is distributed in the hope that it will be useful,                *
*    but WITHOUT ANY WARRANTY; without even the implied                         *
*    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR                    *
*    PURPOSE.  See the GNU Affero General Public License for more details.      *
*                                                                               *
*    You should have received a copy of the GNU Affero General Public           *
*    License along with eLabChem.  If not, see <http://www.gnu.org/licenses/>.  *
*                                                                               *
*   eLabChem is a fork of elabFTW. This file incorporates work covered by the   *
*   copyright notice below.                                                     *                                                               
*                                                                               *
********************************************************************************/

/********************************************************************************
*                                                                               *
*   Copyright 2012-2013 Nicolas CARPi (nicolas.carpi@gmail.com)                 *
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
$load_more_button = "<div class='center'>
        <button class='button' id='loadButton'>Load more</button>
        </div>";

if(isset($_SESSION['prefs']['theme'])) {
require_once("themes/".$_SESSION['prefs']['theme']."/highlight.css");
}
?>



<div id='top-wrapper'>
<div id='submenu'>
    <form id='big_search' method='get' action='experiments.php'>
        <input id='big_search_input' type='search' name='q' size='50' placeholder='Type your search' />
    </form>
    <br />
    <a href="create_item.php?type=exp" class='trigger1' id='exp_create'><img src="themes/<?php echo $_SESSION['prefs']['theme'];?>/img/create.gif" alt="" /> Create experiment</a> | 
    <a href='#' class='trigger'><img src="themes/<?php echo $_SESSION['prefs']['theme'];?>/img/duplicate.png" alt="" /> Create from template</a> |
    <a onmouseover="changeSrc('<?php echo $_SESSION['prefs']['theme'];?>')" onmouseout="stopAnim('<?php echo $_SESSION['prefs']['theme'];?>')" href='experiments.php?mode=show&q=runningonly'><img id='runningimg' src="themes/<?php echo $_SESSION['prefs']['theme'];?>/img/running.fix.png" alt="running" /> Show running experiments</a>
</div><!-- end submenu -->
<div class='toggle_container'><ul>
<?php // SQL to get user's templates
$sql = "SELECT id, name, exp_type FROM experiments_templates WHERE userid = :userid";
$tplreq = $bdd->prepare($sql);
$tplreq->execute(array(
    'userid' => $_SESSION['userid']
));
$count_tpl = $tplreq->rowCount();
if ($count_tpl > 0) {
    while ($tpl = $tplreq->fetch()) {
        echo "<li class='inline'><a href='create_item.php?type=exp&exp_type=".$tpl['exp_type']."&tpl=".$tpl['id']."' class='templates'>".$tpl['name']."</a></li> ";
    }
} else { // user has no templates
    $message = "<strong>You do not have any templates yet.</strong> Go to <a style='color:blue;' href='ucp.php'>your control panel</a> to make one !";
    echo display_message('info', $message);
}
?>
</ul></div>
<div class='create-choices'><ul>
	<li><a href='create_item.php?type=exp' class='templates'>Generic</a></li>
	<li><a href='create_item.php?type=exp&exp_type=chemsingle' class='templates'>Synthetic chemistry</a></li>
</ul></div></div><br />
<?php
// VIEWING PREFS //
$display = $_SESSION['prefs']['display'];
$order = $_SESSION['prefs']['order'];
$sort = $_SESSION['prefs']['sort'];
$limit = $_SESSION['prefs']['limit'];

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
        while ($running_experiments = $req->fetch()) {
            $results_arr[] = $running_experiments['id'];
        }

    // NORMAL SEARCH
    } else {
        $results_arr = search_item('xp', $query, $_SESSION['userid']);
    }

    // show number of results found
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    $unit = 'seconds';
    if ($total_time < 0.01) {
        $total_time = $total_time * 1000;
        $unit = 'milliseconds';
    }
    if (count($results_arr) > 1){
        echo "<p class='results_and_time'>".count($results_arr)." results ($total_time $unit)</p>";
    } elseif (count($results_arr) == 1){
        echo "<p class='results_and_time'>1 result ($total_time $unit)</p>";
    } else {
        $message = 'No experiments were found.';
        echo display_message('error', $message);
    }

    // loop the results array and display results
    foreach($results_arr as $result_id) {
        showXP($result_id, $display);
    }

    // show load more button if there are more results than the default display number
    if (count($results_arr) > $limit) {
        echo $load_more_button;
    }

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
    // show number of results found and time
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    $unit = 'seconds';
    if ($total_time < 0.01) {
        $total_time = $total_time * 1000;
        $unit = 'milliseconds';
    }
    if (count($results_arr) > 1){
        echo "<p class='results_and_time'>".count($results_arr)." results ($total_time $unit)</p>";
    } elseif (count($results_arr) == 1){
        echo "<p class='results_and_time'>1 result ($total_time $unit)</p>";
    } else {
        $message = 'No experiments are linked with this item.';
        echo display_message('error', $message);
    }

    // loop the results array and display results
    foreach($results_arr as $result_id) {
        showXP($result_id, $display);
    } // end foreach

    // show load more button if there are more results than the default display number
    if (count($results_arr) > $limit) {
        echo $load_more_button;
    }

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
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    $unit = 'seconds';
    if ($total_time < 0.01) {
        $total_time = $total_time * 1000;
        $unit = 'milliseconds';
    }
    if (count($results_arr) > 1){
        echo "<p class='results_and_time'>".count($results_arr)." results ($total_time $unit)</p>";
    } elseif (count($results_arr) == 1){
        echo "<p class='results_and_time'>1 result ($total_time $unit)</p>";
    } else {
        $message = 'No experiments were found.';
        echo display_message('error', $message);
    }

    // clean duplicates
    $results_arr = array_unique($results_arr);
    // loop the results array and display results
    foreach($results_arr as $result_id) {
        showXP($result_id, $display);
    } // end foreach

    // show load more button if there are more results than the default display number
    if (count($results_arr) > $limit) {
        echo $load_more_button;
    }

// /////////////////
// DEFAULT VIEW
// /////////////////
} else {
    $sql = "SELECT exp.id, exp.date, rev.rev_title AS title 
        FROM experiments AS exp
        INNER JOIN revisions AS rev
        ON rev.rev_id = exp.rev_id
        WHERE userid_creator = :userid 
        AND status <> 'deleted'
        ORDER BY {$order} {$sort}
        LIMIT 100";
    $req = $bdd->prepare($sql);
	$req->bindValue('userid', $_SESSION['userid'], PDO::PARAM_INT);
    $req->execute();
    $count = $req->rowCount();
    // If there are no experiments, display a little message
    if ($count == 0) {
        $message = "<strong>Welcome to eLabFTW.</strong> 
            Click the <a style='color:blue;' href='create_item.php?type=exp'>
            <img src='themes/".$_SESSION['prefs']['theme']."/img/create.gif' alt='Create experiment' />
            Create experiment</a> button to get started."; 
        echo display_message('info', $message);
    } else {
        while ($experiments = $req->fetch()) {
            $results_arr[] = $experiments['id'];
        }
        $req->closeCursor();
        // loop the results array and display results
        foreach($results_arr as $result_id) {
            showXP($result_id, $display);
        } // end foreach

        // show load more button if there are more results than the default display number
        if (count($results_arr) > $limit) {
            echo $load_more_button;
        }
    }
} // END CONTENT
?>

<script>

<?php
// KEYBOARD SHORTCUTS
echo "key('".$_SESSION['prefs']['shortcuts']['create']."', function(){location.href = 'create_item.php?type=exp'});";
?>
// TOGGLE DIV
$(document).ready(function(){
	$(".toggle_container").hide();
	$(".create-choices").hide();
	$("a.trigger").click(function(){
		$('div.toggle_container').slideToggle(1);
	});
	<?php if(CHEMISTRY) { ?>
		$("#exp_create").attr('href', '#');
		$("a.trigger").mouseover(function(){
			$('div.create-choices').slideUp();
		});
		$("a.trigger1").mouseover(function(){
			$('div.create-choices').slideDown();
			$('div.toggle_container').slideUp();
		});
		$("a.trigger1").click(function(){
			$('div.create-choices').slideDown();
			$('div.toggle_container').slideUp();
		});
		$("#top-wrapper").mouseleave(function(){
			$('div.create-choices').slideUp();
		});
	<?php } ?>
});

// ANIMATE RUNNING ICON
function changeSrc(theme){
    document.getElementById('runningimg').src = 'themes/'+theme+'/img/running.png';
}
function stopAnim(theme){
    document.getElementById('runningimg').src = 'themes/'+theme+'/img/running.fix.png';
}

// READY ? GO !
$(document).ready(function(){

    // SHOW MORE EXPERIMENTS BUTTON
    $('section.item').hide(); // hide everyone
    $('section.item').slice(0, <?php echo $limit;?>).show(); // show only the default at the beginning
    $('#loadButton').click(function(e){ // click to load more
        e.preventDefault();
        $("section.item:hidden").slice(0, <?php echo $limit;?>).show();
        if ($("section.item:hidden").length == 0) { // check if there are more exp to show
            $('#loadButton').hide(); // hide load button when there is nothing more to show
        }
    });

    // EXPERIMENTS TEMPLATE HIDDEN DIV
	$(".toggle_container").hide();
	$("a.trigger").click(function(){
		$('div.toggle_container').slideToggle(1);
	});
    // KEYBOARD SHORTCUTS
    key('<?php echo $_SESSION['prefs']['shortcuts']['create'];?>', function(){
        location.href = 'create_item.php?type=exp'
        });
    });
</script>

