<?php
class Profile {
    private $connection;

    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }
    public function createProfile($userID, $bio="") {
        $bio = addslashes($bio);
        $query = "INSERT INTO profiles (user_id, bio)
                  VALUES ('$userID', '$bio')";
        return mysqli_query($this->connection, $query);
    }
 
    public function updateProfile($userID, $bio) {
        $query = "UPDATE profiles SET bio = '$bio' WHERE user_id = $userID";
        return mysqli_query($this->connection, $query);
    }
    public function deleteProfile($userID) {
        $query = "DELETE FROM profiles WHERE user_id = $userID";
        return mysqli_query($this->connection, $query);
    }

    public function getProfileByUserID($userID) {
        $query = "SELECT * FROM profiles WHERE user_id = $userID";
        $result = mysqli_query($this->connection, $query);
        return mysqli_fetch_assoc($result);
    }

    public function getBio($userID) {
        $query = "SELECT bio FROM profiles WHERE user_id = $userID";
        $result = mysqli_query($this->connection, $query);
        $row = mysqli_fetch_assoc($result);
        return $row ? $row['bio'] : null;
    }
    
    // Just set/update bio
    public function setBio($userID, $bio) {
        return $this->updateProfile($userID, $bio);
    }
    public function getAddress($userID) {
        $query = "SELECT address FROM profiles WHERE user_id = $userID";
        $result = mysqli_query($this->connection, $query);
        $row = mysqli_fetch_assoc($result);
        return $row ? $row['address'] : null;
    }

    public function setAddress($userID, $address) {
        $address = addslashes($address);
        $query = "UPDATE profiles SET address = '$address' WHERE user_id = $userID";
        return mysqli_query($this->connection, $query);
    }

    public function getEducationAndExperience($userID) {
        $query = "SELECT education_and_experience FROM profiles WHERE user_id = $userID";
        $result = mysqli_query($this->connection, $query);
        $row = mysqli_fetch_assoc($result);
        return $row ? $row['education_and_experience'] : null;
    }
    public function setEducationAndExperience($userID, $educationAndExperience) {
        $educationAndExperience = addslashes($educationAndExperience);
        $query = "UPDATE profiles SET education_and_experience = '$educationAndExperience' WHERE user_id = $userID";
        return mysqli_query($this->connection, $query);
    }


    public function updateProfilePicture($userID, $filepath) {
        $filepath = addslashes($filepath);
        $query = "UPDATE profiles SET profile_pic = '$filepath' WHERE user_id = $userID";
        return mysqli_query($this->connection, $query);
    }

    public function updateCoverPicture($userID, $filepath) {
        $checkColumn = "SHOW COLUMNS FROM profiles LIKE 'cover_pic'";
        $result = mysqli_query($this->connection, $checkColumn);
        
        if (mysqli_num_rows($result) == 0) {
            $addColumn = "ALTER TABLE profiles ADD COLUMN cover_pic VARCHAR(255) AFTER profile_pic";
            mysqli_query($this->connection, $addColumn);
        }
        
        $filepath = addslashes($filepath);
        $query = "UPDATE profiles SET cover_pic = '$filepath' WHERE user_id = $userID";
        return mysqli_query($this->connection, $query);
    }
    public function getCoverPicture($userID) {
        $checkColumn = "SHOW COLUMNS FROM profiles LIKE 'cover_pic'";
        $result = mysqli_query($this->connection, $checkColumn);
        
        if (mysqli_num_rows($result) == 0) {
            return null;
        }
        
        $query = "SELECT cover_pic FROM profiles WHERE user_id = $userID";
        $result = mysqli_query($this->connection, $query);
        $row = mysqli_fetch_assoc($result);
        return $row ? $row['cover_pic'] : null;
    }
}
?>
