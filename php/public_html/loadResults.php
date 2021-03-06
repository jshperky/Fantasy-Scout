<?php

// Establish connection to server using init
require 'init.php';



// Formatted .txt files to be loaded into database.
// First line contains table name, second line contains the PHP insert string
$files = array(
  "exampledatafile1.txt",
  "exampledatafile2.txt"
);



// For each file to be loaded, load the formatted .txt file into the database
for ($i = 0; $i < sizeof($files); $i++) {
  // Open the current file
  $file = fopen($files[$i], "r");

  // First line is the table name
  $table = fgets($file);
  // Second line contains week Number
  $weekNum = fgets($file);
  $week = "week" . $weekNum . "Rank";
  // Third line contains the data fields in the database that are being inserted into
  $insertString = fgets($file);



  // Read the rest of the file, each line until the end will contain data to be input into database
  while (!feof($file)) {
    // Values to be inserted into database
    $values = fgets($file);

    // Place data fields into variables
    sscanf($values,"'%s','%s','%s','%s','%u'",$fname,$lname,$pos,$team,$rank);
    // String to check if player with matching first and last name exists
    echo "Values string:".$values."<br>";
    echo "fname string:".$fname."<br>";
    echo "lname string:".$lname."<br>";
    echo "pos string:".$pos."<br>";
    echo "team string:".$team."<br>";
    echo "rank string".$rank."<br>";
    $existingPlayerCheck = "
      SELECT playerID
      FROM $table
      WHERE fname = '$fname' AND lname = '$lname';
    ";
    // Player data string
    $player = mysqli_fetch_assoc(mysqli_query($con, $existingPlayerCheck)) or die(mysqli_error($con));
    $playerID = $player["playerID"];

    // Player already exists, update information
    if ($playerID != 0) {
      $gamesPlayed = $player["gamesPlayed"] + 1;

      // Calculate new average rank
      $avg = 0;
      // Get ranks from all existing weeks
      for ($j = 0; $j < $gamesPlayed-1; $j++) {
        // If week is single digit, add 0 in front to go along with formatting
        // of field naming conventions in database
        if ($j < 10) {
          $avg += $player["week0".$j."Rank"];
        }
        // If double digit, no additional formatting is needed
        else {
          $avg += $player["week".$j."Rank"];
        }
      }
      // Finish average rank calculation
      $avg += $rank;
      $avg /= $gamesPlayed;

      $update = "
        UPDATE Players
        SET $week = $rank, gamesPlayed = $gamesPlayed, avgRank = $avg
        WHERE playerID = $playerID;
      ";
      mysqli_query($con,$update);
    }
    // Player doesn't already exist in database, add new player
    else {
      // String containing SQL INSERT statement to be executed on mysql database to add new player
      $load = "
          INSERT INTO $table ($insertString,gamesPlayed)
          VALUES ($values,1);
      ";
      if (mysqli_query($con, $load)) {
        echo "Team loaded successfully into fsdb<br><br>";
      }
      else {
        echo "Team load FAILURE!:<br>" . mysqli_error($con) . "<br><br>";
      }
    }
  }
  fclose($file);
}


// ***CLOSE CONNECTION WITH SERVER***
if (mysqli_close($con)) {
  echo "Connection to database: FSDB successfully closed.";
}
?>
