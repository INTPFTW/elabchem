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
/* addlink.php - for adding links */
require_once('inc/common.php');

// Check expid is valid and assign it to $item_id
if (isset($_POST['item_id']) && is_pos_int($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
} else {
    die();
}
// Sanitize link
$link_id = filter_var($_POST['link_id'], FILTER_VALIDATE_INT);

// Check expid is owned by connected user
$sql = "SELECT userid_creator FROM experiments WHERE id = ".$item_id;
$req = $bdd->prepare($sql);
$req->execute();
$data = $req->fetch();
if ($data['userid_creator'] == $_SESSION['userid']) {
    // SQL for addlink
    $sql = "INSERT INTO experiments_links (item_id, link_id) VALUES(:item_id, :link_id)";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'item_id' => $item_id,
        'link_id' => $link_id
    ));
    if (!$result) {
        die();
    }
}

