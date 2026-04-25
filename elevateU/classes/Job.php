<?php
class Job {
    private $connection;

    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }
    public function postJob($userID, $title, $description, $location, $type, $salary = '0') {
        $query = "INSERT INTO jobs (user_id, job_name, job_description, location, type, salary)
                  VALUES ('$userID', '$title', '$description', '$location', '$type', '$salary')";
        
        $result = mysqli_query($this->connection, $query);
    
        if (!$result) {
            
            error_log("MySQL Error: " . mysqli_error($this->connection));
        }
    
        return $result;
    }
    public function getAllJobs() {
        $query = "SELECT * FROM jobs ORDER BY job_id DESC";
        $result = mysqli_query($this->connection, $query);

        $jobs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $jobs[] = $row;
        }
        return $jobs;
    }

    public function getJobByID($jobID) {
        $query = "SELECT * FROM jobs WHERE job_id = $jobID";
        $result = mysqli_query($this->connection, $query);
        return mysqli_fetch_assoc($result);
    }
    public function getJobsByUser($userID) {
        $query = "SELECT * FROM jobs WHERE user_id = $userID";
        $result = mysqli_query($this->connection, $query);

        $jobs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $jobs[] = $row;
        }
        return $jobs;
    }

    //public function updateJob($jobID, $title, $description, $location, $type, $deadline) {
    //    $query = "UPDATE jobs 
    //              SET job_name = '$title', job_description = '$description', location = '$location', type = '$type', salary = '0'
    //              WHERE job_id = $jobID";
    //    return mysqli_query($this->connection, $query);
    //}
    public function deleteJob($jobID) {
        $query = "DELETE FROM jobs WHERE job_id = $jobID";
        return mysqli_query($this->connection, $query);
    }
}
?>
