<?php
class Skill {
    private $connection;

    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }
    public function addSkill($profileID, $skillName) {
        $query = "INSERT INTO skills (profile_id, skill_name, endorsement_count)
                  VALUES ('$profileID', '$skillName', 0)";
        return mysqli_query($this->connection, $query);
    }
    public function getSkillsByProfileID($profileID) {
        $query = "SELECT * FROM skills WHERE profile_id = $profileID";
        $result = mysqli_query($this->connection, $query);

        $skills = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $skills[] = $row;
        }
        return $skills;
    }
    public function updateSkill($skillID, $newSkillName) {
        $query = "UPDATE skills SET skill_name = '$newSkillName' WHERE skill_id = $skillID";
        return mysqli_query($this->connection, $query);
    }

    public function endorseSkill($skillID) {
        $query = "UPDATE skills SET endorsement_count = endorsement_count + 1 WHERE skill_id = $skillID";
        return mysqli_query($this->connection, $query);
    }
    public function deleteSkill($skillID) {
        $query = "DELETE FROM skills WHERE skill_id = $skillID";
        return mysqli_query($this->connection, $query);
    }
}
?>
