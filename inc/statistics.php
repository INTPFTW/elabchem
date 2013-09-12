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
// Count number of experiments for each status
// SUCCESS
$status_arr = array();
$count_arr = array();
$status_arr[] = 'success';
$status_arr[] = 'fail';
$status_arr[] = 'redo';
$status_arr[] = 'running';
$status_arr[] = 'deleted';
foreach ($status_arr as $status){
$sql = "SELECT COUNT(id)
    FROM experiments 
    WHERE userid_creator = :userid 
    AND status LIKE'".$status."'";
$req = $bdd->prepare($sql);
$req->bindParam(':userid', $_SESSION['userid']);
$req->execute();
$count_arr[] = $req->fetch();
}

$success = $count_arr[0][0];
$fail = $count_arr[1][0];
$redo = $count_arr[2][0];
$running = $count_arr[3][0];
$deleted = $count_arr[4][0];
// MAKE TOTAL
$total = ($success + $fail + $redo + $running);
// Make percentage
if ($total != 0) {
    $success_p = round(($success / $total)*100);
    $fail_p = round(($fail / $total)*100);
    $redo_p = round(($redo / $total)*100);
    $running_p = round(($running / $total)*100);
	$deleted_p = round(($deleted / $total)*100);
    $total_p = ($success_p + $fail_p + $redo_p + $running_p);

    // BEGIN CONTENT
    echo "<img src='themes/".$_SESSION['prefs']['theme']."/img/statistics.png' alt='' /> <h4>STATISTICS</h4>";
    ?>
    <script src='js/google-jsapi.js'></script>
    <script>
          //google.load('visualization', '1', {packages:['imagepiechart']});
          google.load('visualization', '1', {packages:['corechart']});
          google.setOnLoadCallback(drawChart);
          function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'status');
            data.addColumn('number', 'Experiments number');
            data.addRows([
                ['Running', <?php echo $running_p;?>],
                ['Fail',  <?php echo $fail_p;?>],
                ['Need to be redone',    <?php echo $redo_p;?>],
                ['Success',      <?php echo $success_p;?>],
                ['Deleted',      <?php echo $deleted_p;?>],
                          ]);

            var options = {
                title: 'Experiments for <?php echo $_SESSION['username'];?>',
                backgroundColor: '#EEE'
            }
            var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
            chart.draw(data, options);
          }
        </script>
     <div id="chart_div" class='center'></div>
<?php }else { //end fix division by zero
    echo 'No statistics available yet.';
}
?>

