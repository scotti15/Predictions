<?php // administration.php
require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die("Fatal Error");

//Delete function/////////////////////////////////////////////////////////////
if (isset($_POST['delete']) && isset($_POST['idpredictor']))
{
$idpredictor = get_post($conn, 'idpredictor');
$query = "DELETE FROM predictor WHERE IDPredictor='$idpredictor'";
$result = $conn->query($query);
if (!$result) echo "DELETE failed<br><br>";
}

if (isset($_POST['delete']) && isset($_POST['idleague']))
{
$idleague = get_post($conn, 'idleague');
$query = "DELETE FROM league WHERE IDleague='$idleague'";
$result = $conn->query($query);
if (!$result) echo "DELETE failed<br><br>";
}

if (isset($_POST['delete']) && isset($_POST['idparticipant']))
{
$idparticipant = get_post($conn, 'idparticipant');
$query = "DELETE FROM participant WHERE IDparticipant='$idparticipant'";
$result = $conn->query($query);
if (!$result) echo "DELETE failed<br><br>";
}

if (isset($_POST['delete']) && isset($_POST['idtournament']))
{
$idtournament = get_post($conn, 'idtournament');
$query = "DELETE FROM tournament WHERE IDtournament='$idtournament'";
$result = $conn->query($query);
if (!$result) echo "DELETE failed<br><br>";
}
//////////////////////////////////////////////////////////////////////////////

//Insert Function///////////////////////////////////////////////////////////////

//Add new League
if (!empty($_POST['League'])
)
    {
    $league = get_post($conn, 'League');
    // var_dump($name);
    //   echo "League is $league";
    $insertleague = "INSERT INTO league (LeagueName) VALUES" .
    "('$league')";
    $insertleagueresult = $conn->query($insertleague);
    if (!$insertleagueresult) echo "INSERT League failed<br><br>";
    }

//Add new Name
    if (!empty($_POST['Name'])
)
    {
    $name = get_post($conn, 'Name');
    // var_dump($name);
    // echo $name;
    $insertpredictor = "INSERT INTO predictor (Name) VALUES" .
    "('$name')";
    $insertpredictorresult = $conn->query($insertpredictor);
    if (!$insertpredictorresult) echo "INSERT failed<br><br>";
    }

//Add new Participant
        if (!empty($_POST['FirstName'])
    )
        {
        $firstname = get_post($conn, 'FirstName');
        $lastname = get_post($conn, 'LastName');
        $idleague = get_post($conn, 'LeagueDisplay');
        // var_dump($name);
        //  echo $firstname . $lastname . $idleague;
        $insertparticipant = "INSERT INTO Participant (FirstName, LastName, IDLeagueType) VALUES" .
        "('$firstname', '$lastname', '$idleague')";
        $insertparticipantresult = $conn->query($insertparticipant);
        if (!$insertparticipantresult) echo "INSERT failed<br><br>";
        }

        
//Add new Tournament

if (!empty($_POST['Tournament'])
)
    {
    $name = get_post($conn, 'Tournament');
    $description = get_post($conn, 'Description');
    $year = get_post($conn, 'Year');
    $rounds = get_post($conn, 'Rounds');
    $favourites = get_post($conn, 'Favourites');
    $idleague = get_post($conn, 'LeagueDisplay');
    // var_dump($name);
    //  echo $firstname . $lastname . $idleague;
    $inserttournament = "INSERT INTO tournament (Name, Description, Year, Rounds, Favourites, IDLeagueType) VALUES" .
    "('$name', '$description', '$year', '$rounds', '$favourites', '$idleague')";
    $inserttournamentresult = $conn->query($inserttournament);
    if (!$inserttournamentresult) echo "INSERT failed<br><br>";
    
    $tid = $conn->query('Select Max(IDTournament) as MaxTournament From tournament');
    $tournamentid = $tid->fetch_array()[0] ?? '';
    for ($r = $rounds; $r > 0 ; --$r) {
        for ($m = 1; $m < (pow(2, $r)) / 2 + 1; ++$m) {
            $round = $rounds - $r + 1;
            $matchupid = $tournamentid . '-' . $round  . '-' . sprintf('%02d', $m);
            $nextround = ceil($m / 2);
            if ($round != $rounds) {
                $nextmatchupid = $tournamentid . '-' . ($round+1)  . '-' . sprintf('%02d', $nextround);
            } else {
                $nextmatchupid = null;
            }
            $insertmatchups = "INSERT Into matchup (IDMatchup, Round, IDTournament, IDNextMatchup) Values " .   "('$matchupid','$round','$tournamentid','$nextmatchupid')";
            $inserttournamentmatchups = $conn->query($insertmatchups);           
            if (!$inserttournamentmatchups) echo "INSERT matchups failed<br><br>";

        }
    }
    $populateprediction = "INSERT INTO prediction (IDMatchup, IDPredictor) SELECT IDMatchup, IDPredictor From matchup, predictor Where IDTournament = '$tournamentid'";
    $populatepredictionresult = $conn->query($populateprediction);
    if (!$populatepredictionresult) echo "INSERT prediction failed<br><br>";
}
////////////////////////////////////////////////////////////////////////////////
//Display Application Fact tables

$sql = "SELECT * FROM `league`";
$all_leagues = mysqli_query($conn,$sql);
echo <<<_END
<form action="administration.php" method="post" id="submitform">
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





<?php
$getpredictors = "SELECT * FROM predictor";
$predictorlist = $conn->query($getpredictors);
if (!$predictorlist) die("Database access failed");
$rows = $predictorlist->num_rows;
for ($j = 0; $j < $rows; ++$j) {
    $row = $predictorlist->fetch_array(MYSQLI_NUM);
    $r0 = htmlspecialchars($row[0]);
    $r1 = htmlspecialchars($row[1]);
    echo <<<_END
<pre>
</pre>
<form action='administration.php' method='post'>
<input type='hidden' name='delete' value='yes'>
<input type='hidden' name='idpredictor' value='$r0'>
$r1 <input type='submit' value='Delete Predictor'></form>
_END;
}
$predictorlist->close();


$getleagues = "SELECT * FROM league";
$leaguelist = $conn->query($getleagues);
if (!$leaguelist) die("Database access failed");
$rows = $leaguelist->num_rows;
for ($j = 0; $j < $rows; ++$j) {
    $row = $leaguelist->fetch_array(MYSQLI_NUM);
    $r0 = htmlspecialchars($row[0]);
    $r1 = htmlspecialchars($row[1]);
    echo <<<_END

<form action='administration.php' method='post'>
<input type='hidden' name='delete' value='yes'>
<input type='hidden' name='idleague' value='$r0'>
$r1 <input type='submit' value='Delete League'></form>
_END;
}
$leaguelist->close();


$getparticipants = "SELECT IDParticipant, FirstName, LastName, LeagueName FROM participant Join league on participant.IDLeagueType = league.IDLeague";
$participantlist = $conn->query($getparticipants);
if (!$participantlist) die("Database access failed");
$rows = $participantlist->num_rows;
for ($j = 0; $j < $rows; ++$j) {
    $row = $participantlist->fetch_array(MYSQLI_NUM);
    $r0 = htmlspecialchars($row[0]);
    $r1 = htmlspecialchars($row[1]);
    $r2 = htmlspecialchars($row[2]);
    $r3 = htmlspecialchars($row[3]);
    echo <<<_END
<pre>
</pre>
<form action='administration.php' method='post'>
<input type='hidden' name='delete' value='yes'>
<input type='hidden' name='idparticipant' value='$r0'>
$r1 $r2 $r3<input type='submit' value='Delete Participant'></form>
_END;
}

$participantlist->close();


$gettournaments = "SELECT IDTournament, Name, Description, Year, Rounds, Favourites, LeagueName FROM tournament Join league on tournament.IDLeagueType = league.IDLeague";
$tournamentlist = $conn->query($gettournaments);
if (!$tournamentlist) die("Database access failed");
$rows = $tournamentlist->num_rows;
for ($j = 0; $j < $rows; ++$j) {
    $row = $tournamentlist->fetch_array(MYSQLI_NUM);
    $r0 = htmlspecialchars($row[0]);
    $r1 = htmlspecialchars($row[1]);
    $r2 = htmlspecialchars($row[2]);
    $r3 = htmlspecialchars($row[3]);
    $r4 = htmlspecialchars($row[4]);
    $r5 = htmlspecialchars($row[5]);
    $r6 = htmlspecialchars($row[6]);
    echo <<<_END
<pre>
</pre>
<form action='administration.php' method='post'>
<input type='hidden' name='delete' value='yes'>
<input type='hidden' name='idtournament' value='$r0'>
$r1 $r2 $r3 $r4 $r5 $r6<input type='submit' value='Delete Tournament'></form>
_END;
}

$tournamentlist->close();
////////////////////////////////////////////////////////////////////////////////

$conn->close();
function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}
