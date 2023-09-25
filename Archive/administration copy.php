<?php // administration copy.php
require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die("Fatal Error");

$sql = "SELECT * FROM `tournament`";
$all_tournaments = mysqli_query($conn,$sql);

$sql = "SELECT * FROM `league`";
$all_leagues = mysqli_query($conn,$sql);
var_dump($_POST);
echo <<<_END
<form action="administration copy.php" method="post" id="submitform">
<pre>
Name <input type="text" name="Name"> <input type="submit" value="Add New Predictor">
League <input type="text" name="League"> <input type="submit" value="Add New League">
First Name <input type="text" name="FirstName"> Last Name <input type="text" name="LastName"> <input type="submit" value="Add New Participant">
Tournament Name <input type="text" name="Tournament"> Description <input type="text" name="Description">Year<input type="text" name="Year">  Rounds<input type="text" name="Rounds">  Favourites<input type="text" name="Favourites"> <input type="submit" value="Add New Tournament">

</pre>
</form> 
_END;
?>
<select name="LeagueDisplay" form='submitform'>
    <?php
    // use a while loop to fetch data
    // from the $all_categories variable
    // and individually display as an option
    while ($leaugeTable = mysqli_fetch_array(
            $all_leagues,MYSQLI_ASSOC)):;
?>
    <option value="<?php echo $leaugeTable["IDLeague"];
        // The value we usually set is the primary key
    ?>">
        <?php echo $leaugeTable["LeagueName"];
            // To show the category name to the user
        ?>
    </option>
<?php
    endwhile;
    // While loop must be terminated
?>
</select> 

<select name="TournamentDisplay" form='submitform'>
<?php
// use a while loop to fetch data
// from the $all_categories variable
// and individually display as an option
while ($tournamentTable = mysqli_fetch_array(
        $all_tournaments,MYSQLI_ASSOC)):;
?>
<option value="<?php echo $tournamentTable["IDTournament"];
    // The value we usually set is the primary key
?>">
    <?php echo $tournamentTable["Name"];
        // To show the category name to the user
    ?>
</option>
<?php
endwhile;
// While loop must be terminated
?>

</select> 


<?php
function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}
