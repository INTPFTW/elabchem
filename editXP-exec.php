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

//Array to store validation errors
$msg_arr = array();
//Validation error flag
$errflag = false;

// CHECKS
// ID
if(is_pos_int($_POST['item_id'])){
    $id = $_POST['item_id'];
} else {
    $id='';
    $msg_arr[] = 'The id parameter is not valid !';
    $errflag = true;
}
$title = check_title($_POST['title']);
$date = check_date($_POST['date']);
$body = check_body($_POST['body']);
$status = check_status($_POST['status']);

// Store stuff in Session to get it back if error input
$_SESSION['new_title'] = $title;
$_SESSION['new_date'] = $date;
$_SESSION['new_status'] = $status;

// If input errors, redirect back to the experiment form
if($errflag) {
    $_SESSION['errors'] = $msg_arr;
    session_write_close();
    header("location: experiments.php?mode=show&id=$id");
    exit();
}

// SQL for editXP
    $sql = "UPDATE experiments 
        SET title = :title, 
        date = :date, 
        body = :body, 
        status = :status
        WHERE userid_creator = :userid 
        AND id = :id";
$req = $bdd->prepare($sql);
$result = $req->execute(array(
    'title' => $title,
    'date' => $date,
    'body' => $body,
    'status' => $status,
    'userid' => $_SESSION['userid'],
    'id' => $id
));


// Check if insertion is successful
if($result) {
    unset($_SESSION['new_title']);
    unset($_SESSION['new_date']);
    unset($_SESSION['status']);
    unset($_SESSION['errors']);
    header("location: experiments.php?mode=view&id=$id");
} else {
    die('Something went wrong in the database query. Check the flux capacitor.');
}
?>
