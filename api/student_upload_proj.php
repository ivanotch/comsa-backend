<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['projectTitle'];
    $projType = $_POST["projectType"];
    $projDesc = $_POST["projectDescription"];

    //tech and team members separated by space
    //images

    $projDownloadLink = $_POST["downloadLink"];
    $projLiveLink = $_POST["liveLink"];
    $projGithubLink = $_POST["githubLink"];

    
}
