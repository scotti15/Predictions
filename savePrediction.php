<?php // administration.php
require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die("Fatal Error");


if (isset($_POST ["predicted"])) { 

    $sql = "SELECT pr.Name, Concat(pa.FirstName,' ',pa.LastName) as Winner, IDMatchup FROM `prediction` p Left Join participant pa on p.IDWinner = pa.IDParticipant JOIN predictor pr on p.IDPredictor = pr.IDPredictor";
    $all_predictions = mysqli_query($conn,$sql);
    
    $sql = "SELECT Distinct p.IDPredictor, p.Name From predictor p Join prediction pr on p.IDPredictor = pr.IDPredictor";
    $all_predictors = mysqli_query($conn,$sql);

    $numberofpredictors = $all_predictors->num_rows;
    $numberofpredictions = $all_predictions->num_rows;


$n = 0;        
$i = 0;
while ($row = mysqli_fetch_array($all_predictors, MYSQLI_ASSOC)) {
    $PredictorList[$i] = $row["Name"];
    $i++;
}
    
$i = 0;
while ($row = mysqli_fetch_array($all_predictions, MYSQLI_ASSOC)) {
    $Name[$i] = $row["Name"];
    $Winner[$i] = $row["Winner"];
    $Idmatchup[$i] = $row["IDMatchup"];
    $i++;
}
// print_r($Name);
// echo "\xA";
// print_r($Winner);
// echo "\xA";
// print_r($Idmatchup);
// echo "\xA";
// die();
 
    // var_dump("Predicted");
    $predicted = $_POST["predicted"];
    $predictor = $_POST["PredictorDisplay"];
    $idunderdog = $_POST["IDUnderdog"];
    $idfavourite = $_POST["idFavourite"];
    $idmatchup = $_POST["idmatchup"];
    $numberofgames = $_POST["numberofgames"];

    $winner = $predicted == "Favourite" ? $idfavourite : $idunderdog;

    // $numberofgames = $_POST["numberofgames"];
    // $idfavourite = $_POST["idFavourite"];
    // $favourite = $_POST["Favourite"];
    // $idunderdog = $_POST["IDUnderdog"];
    // $underdog = $_POST["Underdog"];
    // $idmatchup = $_POST["idmatchup"];
    // $predicted = $_POST["predicted"];
    // var_dump("Number of Games = ".$numberofgames);
    // var_dump("IDFavourite = ".$idfavourite);
    // var_dump("Favourite = ".$favourite);
    // var_dump("idUnderdog = ".$idunderdog);
    // var_dump("Underdog = ".$underdog);
    // var_dump("ID Matchup = ".$idmatchup);
    // var_dump("Predicted = ".$predicted);
    // var_dump("Winner = ".$winner);
    // die();

    $saveprediction = "UPDATE prediction SET IDWinner = $winner, Games = $numberofgames WHERE IDMatchup = '$idmatchup' AND IDPredictor = $predictor";
    $savepredictionresult = $conn->query($saveprediction);
    if (!$savepredictionresult) die("Select From matchup access failed");

} else {
    var_dump("Nothing Predicted");
};

header("Location: testpage.php");


    ?>
<table>
    <tr>
<?php        
for ($j = 0; $j < $numberofpredictors; ++$j) {

    echo <<<_END
    <td>$PredictorList[$j]</td>
_END;
} 
?>
</tr>
<tr>
<?php    

for ($j = 0; $j < $numberofpredictions; ++$j) {
    echo <<<_END
    <td>$Winner[$j]</td>
    <tr></tr>
_END;
}
?>
</tr>
</table>
<?php
die();

// <form action='matchupManager.php' method='post'>
// <input type='hidden' name='delete' value='yes'>
// <input type='hidden' name='idmatchup' value='$r0'>
// $r0 
// <input type='text' name='fseed' value='$r8'>
// <input type='text' name='Participant' value='$r3'>
// <input type='text' name='fscore' value='$r5' >
// <br>     
// $r0
// <input type='text' name='useed' value='$r9'>
// <input type='text' name='Underdog' value='$r4'>
// <input type='text' name='uscore' value='$r6'>
// <input type='submit' value='Delete matchup'>
?>
<table>
    <tr>
        <td>Ian</td>
        <td>Michael</td>
        <td>Paula</td>
    </tr>
    <tr>
        <td>Boston</td>
        <td>Boston</td>
        <td>Florida</td>
    </tr>
    <tr>
        <td>Ranger</td>
        <td>Islanders</td>
        <td>Islanders</td>
    </tr>
</table>