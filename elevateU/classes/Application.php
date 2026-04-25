<?php 
class Application {
    private $connection;
    // fuck off ya razan
    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }
    public function applyToJob($jobID, $userID, $resumeFile, $applicationDescription, $status = 'pending') {
        $stmt = $this->connection->prepare(
            "INSERT INTO applications (job_id, user_id, resume, application_description, status) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iisss", $jobID, $userID, $resumeFile, $applicationDescription, $status);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getApplicationsByUser($userID) {
        $query = "SELECT * FROM applications WHERE user_id = $userID ORDER BY timestamp DESC";
        $result = mysqli_query($this->connection, $query);

        $applications = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $applications[] = $row;
        }
        return $applications;
    }

    public function getApplicantsByJob($jobID) {
         $query = "SELECT a.*, u.name AS applicant_name FROM applications a JOIN users u ON a.user_id = u.userID WHERE a.job_id = $jobID";
        $result = mysqli_query($this->connection, $query);

        $applicants = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $applicants[] = $row;
        }
        return $applicants;
    }

    public function updateApplicationStatus($applicationID, $newStatus) {
        $query = "UPDATE applications SET status = '$newStatus' WHERE application_id = $applicationID";
        return mysqli_query($this->connection, $query);
    }
    
    public function deleteApplication($applicationID) {
        $query = "DELETE FROM applications WHERE application_id = $applicationID";
        return mysqli_query($this->connection, $query);
    }
    public function getAllApplications() {
        $query = "SELECT * FROM applications";
        $result = mysqli_query($this->connection, $query);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
?>
