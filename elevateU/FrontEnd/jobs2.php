<?php
session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../FrontEnd/register.php');
    exit;
}
require_once "../DBController.php";
require_once "../classes/Job.php";
require_once "../classes/Application.php";

$db = new DBController();
$db->openConnection();
$conn = $db->connection;
$jobObj = new Job($conn);
$appObj = new Application($conn);

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'employer') {
    header("Location: ../FrontEnd/register.php");
    exit;
}

$userID = isset($_SESSION['user']['userID']) ? $_SESSION['user']['userID'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jobTitle'])) {
    $title = $_POST['jobTitle'];
    $type = $_POST['jobType'];
    $location = $_POST['location'];
    $salary = $_POST['salary'] ?: '0'; // Use 0 if empty
    $description = $_POST['description'];
    $requirements = $_POST['requirements'];
    $skills = $_POST['skills'];
    $deadline = date('Y-m-d', strtotime('+30 days'));

    $success = $jobObj->postJob($userID, $title, $description, $location, $type, $salary);
    if ($success) {
        echo "✅ Job posted successfully.";
    } else {
        echo "❌ Failed to post job.";
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'getApplicants' && isset($_GET['jobID'])) {
    $applicants = $appObj->getApplicantsByJob($_GET['jobID']);
    echo json_encode($applicants);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'updateStatus') {
    $appID = $_POST['appID'];
    $status = $_POST['status'];
    $appObj->updateApplicationStatus($appID, $status);
    echo "Status updated";
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'deleteJob' && isset($_POST['jobID'])) {
    $jobID = $_POST['jobID'];
    $jobObj->deleteJob($jobID);
    echo "Job deleted";
    exit;
}

$myJobs = $jobObj->getJobsByUser($userID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>elevateU - Employer Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <style>
         body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fc;
            color: #5a5a5a;
        }
        .main-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 24px;
            border: 1px solid #f0f0f0;
        }

        .card-header {
            background-color: #007b8f;
            
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            color: whitesmoke;
            font-weight: 600;
            font-size: 18px;
        }

        .card-body {
            padding: 20px;
        }
        .button {
            
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .primary-button {
            background-color: #0b2239;
            color: white;
            border: none;
        }

        .primary-button:hover {
            background-color: #00bfa6;
            color: white;
        }


        .secondary-button {
            background-color: #f0f0f0;
            color: #333;
        }

        .secondary-button:hover {
            background-color: #e0e0e0;
        }
        .job-list {
            
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .job-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 15px;
            transition: box-shadow 0.2s;
        }

        .job-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .job-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .job-details {
            
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }

        .job-detail {
            
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .job-actions {
            
            display: flex;
            gap: 10px;
        }
        .applicant-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .applicant-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 15px;
        }

        .applicant-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .applicant-email {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .applicant-actions {
            display: flex;
            gap: 10px;
        }

        /* Form styles */
        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            min-height: 100px;
            resize: vertical;
        }

        .form-select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            background-color: white;
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background-color: #dcfce7;
            color: #10b981;
        }

        .status-pending {
            background-color: #fff7ed;
            color: #f59e0b;
        }

        .status-approved {
            background-color: #dcfce7;
            color: #10b981;
        }

        .status-rejected {
            background-color: #fef2f2;
            color: #ef4444;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .tab {
            font-family: 'Poppins', sans-serif;
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 500;
            color: #666;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        .tab:hover {
            color: #4a6cf7;
        }

        .tab.active {
            color: #4a6cf7;
            border-bottom-color: #4a6cf7;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {
            .job-details {
                flex-direction: column;
                gap: 5px;
            }
            
            .job-actions {
                flex-direction: column;
                gap: 5px;
            }
            
            .applicant-actions {
                flex-direction: column;
                gap: 5px;
            }
            
            .button {
                width: 100%;
            }
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 18px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            position: fixed;
            top: 18px;
            right: 40px;
            transition: background 0.2s;
            z-index: 9999;
        }
        .logout-btn:hover {
            background: #b91c1c;
        }
    </style>
    <link rel="stylesheet" href="employer.css">
</head>
<body>
<form method="get" action="jobs2.php" style="margin:0;">
    <button type="submit" name="logout" class="logout-btn">Logout</button>
</form>
<div class="main-container">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Employer Dashboard</div>
        </div>
        <div class="card-body">
            <div class="tabs">
                <div class="tab active" data-tab="jobs">My Jobs</div>
                <div class="tab" data-tab="post">Post a Job</div>
                <div class="tab" data-tab="applicants">Applicants</div>
            </div>
            <div class="tab-content active" id="jobs-tab">
                <div class="job-list">
                    <?php if (empty($myJobs)): ?>
                        <p>No jobs posted yet. Post your first job!</p>
                    <?php else: ?>
                        <?php foreach ($myJobs as $job): ?>
                        <div class="job-card">
                            <div class="job-title"><?php echo htmlspecialchars($job['job_name']); ?></div>
                            <div class="job-details">
                                <div class="job-detail"><?php echo htmlspecialchars($job['location']); ?></div>
                                <div class="job-detail">Salary: $<?php echo htmlspecialchars($job['salary']); ?></div>
                                <div class="job-detail">Posted: <?php echo htmlspecialchars($job['timestamp']); ?></div>
                                <div class="job-detail">
                                    <span class="status-badge status-active">Active</span>
                                </div>
                            </div>
                            <div class="job-actions">
                                <button class="button primary-button view-applicants-btn" data-job-id="<?php echo $job['job_id']; ?>" data-job-title="<?php echo htmlspecialchars($job['job_name']); ?>">View Applicants</button>
                                <button class="button secondary-button delete-job-btn" data-job-id="<?php echo $job['job_id']; ?>">Delete</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="tab-content" id="post-tab">
                <form id="post-job-form">
                    <div class="form-group">
                        <label class="form-label">Job Title</label>
                        <input type="text" class="form-input" name="jobTitle" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Job Type</label>
                        <select class="form-select" name="jobType" required>
                            <option value="">Select Job Type</option>
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            
                            <option value="internship">Internship</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <select class="form-select" name="location" required>
                            <option value="">Select Location</option>
                            <option value="remote">Remote</option>
                            <option value="on-site">On-site</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Salary</label>
                        <input type="text" class="form-input" name="salary" placeholder="e.g., 50000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-textarea" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Requirements</label>
                        <textarea class="form-textarea" name="requirements"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Skills</label>
                        <input type="text" class="form-input" name="skills" placeholder="e.g., PHP, MySQL, JavaScript">
                    </div>
                    <button type="submit" class="button primary-button">Post Job</button>
                </form>
            </div>
            
            <div class="tab-content" id="applicants-tab">
                <div id="applicants-container">
                    <p>Select a job to view its applicants.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(`${tab.getAttribute('data-tab')}-tab`).classList.add('active');
        });
    });

    document.getElementById('post-job-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('jobs2.php', {
            method: 'POST',
            body: formData
        }).then(res => res.text()).then(result => {
            alert(result);
            if (result.includes("✅")) location.reload();
        });
    });

    document.querySelectorAll('.view-applicants-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const jobId = this.dataset.jobId;
            const jobTitle = this.dataset.jobTitle;
            document.querySelector('.tab[data-tab="applicants"]').click();
            fetch(`jobs2.php?action=getApplicants&jobID=${jobId}`)
                .then(res => res.json())
                .then(data => {
                    let html = `<h3>Applicants for ${jobTitle}</h3>`;
                    if (data.length === 0) {
                        html += `<p>No applicants yet.</p>`;
                    } else {
                        html += '<div class="applicant-list">';
                        data.forEach(app => {
                            html += `
                                <div class="applicant-card">
                                    <div class="applicant-name">Name: ${app.applicant_name}</div>
                                    <div class="applicant-email">Status: <span class="status-badge status-${app.status}">${app.status}</span></div>
                                    <div class="applicant-email">Description: ${app.application_description || 'No description provided'}</div>
                                    <div class="applicant-actions">
                                        ${app.resume ? `<a href="../uploads/${app.resume}" class="button primary-button" download>Download Resume</a>` : '<span>No resume uploaded</span>'}
                                        <button class="button primary-button" onclick="updateStatus(${app.application_id}, 'accepted')">Approve</button>
                                        <button class="button secondary-button" onclick="updateStatus(${app.application_id}, 'rejected')">Reject</button>
                                    </div>
                                </div>`;
                        });
                        html += '</div>';
                    }
                    document.getElementById('applicants-container').innerHTML = html;
                });
        });
    });

    document.querySelectorAll('.delete-job-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this job?')) {
                const jobId = this.dataset.jobId;
                fetch('jobs2.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=deleteJob&jobID=${jobId}`
                })
                .then(res => res.text())
                .then(response => {
                    alert(response);
                    location.reload();
                });
            }
        });
    });
});

function updateStatus(appID, status) {
    fetch('jobs2.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=updateStatus&appID=${appID}&status=${status}`
    })
    .then(res => res.text())
    .then(response => {
        alert(response);
        location.reload();
    });
}
</script>
</body>
</html>
