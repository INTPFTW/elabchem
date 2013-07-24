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
require_once('inc/common.php');
$msg_arr = array();

// What do we create ?
if (isset($_GET['type']) && !empty($_GET['type']) && is_pos_int($_GET['type'])) {
    // $type is int here
    $type = $_GET['type'];
} elseif (isset($_GET['type']) && !empty($_GET['type']) && ($_GET['type'] === 'exp')){
    $type = 'experiments';
} else {
    $msg_arr[] = 'Wrong item type !';
    $_SESSION['infos'] = $msg_arr;
    header('location: index.php');
    exit();
}


if ($type === 'experiments'){
    $elabid = generate_elabid();
    // do we want template ?
    if(isset($_GET['tpl']) && is_pos_int($_GET['tpl'])) {
        // SQL to get template
        $sql = "SELECT body FROM experiments_templates WHERE id = :id";
        $get_tpl = $bdd->prepare($sql);
        $get_tpl->execute(array(
            'id' => $_GET['tpl']
        ));
        $get_tpl_body = $get_tpl->fetch();
        $body = $get_tpl_body['body'];
    } else {
        $body = '';
    }

    // SQL for create experiments
    $sql = "INSERT INTO experiments(title, date, body, status, elabid, userid_creator) VALUES(:title, :date, :body, :status, :elabid, :userid)";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'title' => 'Untitled',
        'date' => kdate(),
        'body' => $body,
        'status' => 'running',
        'elabid' => $elabid,
        'userid' => $_SESSION['userid']
    ));
} else { // create item for DB
    // SQL to get template
    $sql = "SELECT template FROM items_types WHERE id = :id";
    $get_tpl = $bdd->prepare($sql);
    $get_tpl->execute(array(
        'id' => $type
    ));
    $get_tpl_body = $get_tpl->fetch();

    // SQL for create DB item
    $sql = "INSERT INTO items(title, date, body, userid, type) VALUES(:title, :date, :body, :userid, :type)";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'title' => 'Untitled',
        'date' => kdate(),
        'body' => $get_tpl_body['template'],
        'userid' => $_SESSION['userid'],
        'type' => $type
    ));
}

// Get what is the item id we just created
if ($type === 'experiments') {
    $sql = "SELECT id FROM experiments WHERE userid_creator = :userid ORDER BY id DESC LIMIT 0,1";
} else {
    $sql = "SELECT id FROM items WHERE userid = :userid ORDER BY id DESC LIMIT 0,1";
}
$req = $bdd->prepare($sql);
$req->bindParam(':userid', $_SESSION['userid']);
$req->execute();
$data = $req->fetch();
$newid = $data['id'];

// Check if insertion is successful and redirect to the newly created experiment in edit mode
if($result) {
    // info box
    $msg_arr[] = 'New item successfully created.';
    $_SESSION['infos'] = $msg_arr;
    if ($type === 'experiments') {
        header('location: experiments.php?mode=edit&id='.$newid.'');
    } else {
        header('location: database.php?mode=edit&id='.$newid.'');
    }
} else {
    die("Something went wrong in the database query. Check the flux capacitor.");
}
?>
