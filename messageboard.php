<?php 


require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die("Fatal Error");
//var_dump($_POST);

//Begin mark message as read
    if(isset($_POST['sessionpredictor']) && isset($_POST['MarkAsRead'])) {
    $idmessage = get_post($conn, 'idmessage');
    $idpredictor = get_post($conn, 'sessionpredictor');
// var_dump("Message # ".$idmessage." and Predictor # ".$idpredictor);
// die();
    $insertreadmessages = "INSERT INTO readmessages (IDMessage, IDPredictor) VALUES " .
    "($idmessage, $idpredictor)";

    // var_dump($insertreadmessages);
    // die();
    $result = $conn->query($insertreadmessages);
    if (!$result) echo "Mark as Read failed<br><br>";
    }
//End mark message as read

// Begin Add New Message
    if(isset($_POST['sessionpredictor']) && isset($_POST['AddMessage']) && !isset($_POST['MarkAsRead'])) {

    $predictor=$_POST['sessionpredictor'];
    $message = get_post($conn, 'Message');
 
    if(!empty($message))
    {

        $getpredictorname = "SELECT * FROM `predictor` Where idpredictor = $predictor";

        $addnewmessage = "INSERT INTO messageboard (Message, IDPredictor) VALUES" .
        "('$message', '$predictor')";
        $addnewmessageresult = $conn->query($addnewmessage);
        if (!$addnewmessageresult) echo "INSERT failed<br><br>";
    
        // Get ID of message just added
        $getmessageid = "SELECT Max(IDMessage) as messageid From messageboard";
        $getmessageidresults = $conn->query($getmessageid);
        $row = $getmessageidresults->fetch_array(MYSQLI_NUM);
        $idmessage = htmlspecialchars($row[0]);
        // Insert the message the user just added to readmessages
        $insertreadmessages = "INSERT INTO readmessages (IDMessage, IDPredictor) VALUES " .
        "($idmessage, $predictor)";
    
        $result = $conn->query($insertreadmessages);
        if (!$result) echo "Mark as Read failed<br><br>";
    }
    }


// End Add New Message



if(isset($_POST['sessionpredictor'])) {
    $predictor=$_POST['sessionpredictor'];

    $getpredictorname = "SELECT * FROM `predictor` Where idpredictor = $predictor";
    
    $getpredictornameresults = $conn->query($getpredictorname);
    $row = $getpredictornameresults->fetch_array(MYSQLI_NUM);
    $predictorname = htmlspecialchars($row[1]);

    $admin = $predictor != 28 ? 'hidden' : '';
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Message Board</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round|Open+Sans">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <style type="text/css">
    .bs-example{
    margin: 20px;
    }
    </style>
    </head>
<body>

<form action="Predictions.php" method="post" id="submitform" >
<input type="submit" <?php echo $admin ?> value="Admin Tools" name="AdminTools" >
<input type="submit"  <?php echo $admin ?> value="Tournament Management" name="TournamentManagementTools">
<input type="submit" value="Prediction Page" name="PredictionTools">
<input type="submit" value="Results Page" name="ResultsPage">
<input type="hidden" name='sessionpredictor' value=<?php echo $predictor ?>>
</form> 

<form action="messageboard.php" method="post" id="addnewmessage">
<input type="hidden"  value='<?php echo $predictor ?>' name="sessionpredictor">
<input type="hidden" value='<?php echo $predictorname ?>' name="sessionpredictorname">
Messages<input type="textarea" class="form-control" name="Message" placeholder='Enter your message here'> <input type="submit" value="Add New Message" name="AddMessage" >
</form>

    <?php
    
$getmessages = "SELECT Distinct Message
                        , p.Name as Predictor
                        , Case When r.IDPredictor = $predictor then '' Else 'background: #448ec2; color:#ffffff; font-weight:bold' End as MessageRead
                        , m.IDMessage
                        , m.IDPredictor
                From messageboard m 
                Join predictor p on m.IDPredictor = p.IDPredictor
                Left Join (Select IDMessage, IDPredictor From readmessages Where IDPredictor = $predictor) r on m.IDMessage = r.IDMessage
                Order by m.IDMessage Desc;";
    // var_dump($getmessages);
    // die();
    $result = mysqli_query($conn,$getmessages);
?>
<h2 class="pull-left">Messages  for <?php echo $predictorname ?></h2>
        <table class='table table-bordered table-striped'>
        <tr>
        <td width='80%' style='color : #ffffb3; background:#000000; font-weight:bold'>Message</td>
        <td width='10%' style='color : #ffffb3; background:#000000; font-weight:bold'>Posted By</td>
        <td width='10%' style='color : #ffffb3; background:#000000; font-weight:bold'>Action</td>
        </tr>
        </table>
        <?php
        $i=0;
        while($row = mysqli_fetch_array($result)) {
            ?>
            <table class='table table-bordered table-striped'>
            <tr>
            <td width='80%' style = '<?php echo $row["MessageRead"]; ?>'><?php echo $row["Message"]; ?></td>
            <td width='10%' style = '<?php echo $row["MessageRead"]; ?>'><?php echo $row["Predictor"]; ?></td>
            <td><form action='messageboard.php' method='post'><input type='submit' name='MarkAsRead' value='Mark As Read' >
                <input type='hidden' name='idmessage' value='<?php echo $row["IDMessage"]; ?>'>
                <input type='hidden' name='sessionpredictor' value='<?php echo $predictor; ?>'>
            </form>
            </td> 
            </tr>
            <?php
            $i++;
            ?>
            </table>
</body>


<?php
        }


} else {
    header("Location: OpenSession.php");
}

function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}
