<?php
session_start();
if (isset($_GET['applied'])) {
    $msg = $_GET['applied'] == 1
        ? "✅ Application submitted successfully!"
        : "❌ Failed to submit application.";
    echo "<script>alert('$msg');</script>";
}
require_once "../DBController.php";
require_once "../classes/Job.php";
require_once "../classes/Application.php";
$db = new DBController();
$db->openConnection();
$conn = $db->connection;

if (!isset($_SESSION['user'])) {
    header("Location: register.php");
    exit;
}
$jobObj = new Job($conn);
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $jobs = array_filter($jobObj->getAllJobs(), function($job) use ($search) {
        $search = strtolower($search);
        return strpos(strtolower($job['job_name']), $search) !== false
            || strpos(strtolower($job['job_description']), $search) !== false
            || strpos(strtolower($job['location']), $search) !== false;
    });
} else {
    $jobs = $jobObj->getAllJobs();
}

$applicationObj = new Application($conn);
$userID = $_SESSION['user']['userID'];
$userApplications = $applicationObj->getApplicationsByUser($userID);

$uniqueApplications = [];
foreach (array_reverse($userApplications) as $app) {
    if (!isset($uniqueApplications[$app['job_id']])) {
        $uniqueApplications[$app['job_id']] = $app;
    }
}
$uniqueApplications = array_values($uniqueApplications);
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>elevateU - Jobs</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <style>
        .main-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }


        .card {
            font-family: 'Poppins', sans-serif;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 24px;
            border: 1px solid #f0f0f0;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-weight: 600;
            font-size: 18px;
            color: #333;
        }

        .card-body {
            padding: 20px;
        }

        /* Search and filter section */
        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-button {
            background-color: #0b2239;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0 20px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            font-family: 'Poppins', sans-serif;
        }

        .search-button:hover {
            background-color: #007b8f;
        }

        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            background-color: white;
        }

        /* Job listings */
        .job-list {

            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .job-card {
            font-family: 'Poppins', sans-serif;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .job-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .job-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .job-logo {
            width: 60px;
            height: 60px;
            background-color: #f0f4ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #00bfa6;
            font-size: 24px;
            flex-shrink: 0;
        }

        .job-title-container {
            flex: 1;
        }

        .job-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .job-company {
            font-size: 15px;
            color: #555;
            margin-bottom: 5px;
        }

        .job-location {
            font-size: 14px;
            color: #666;
            display: flex;
            align-items: center;
        }

        .location-icon {
            margin-right: 5px;
        }

        .job-description {
            margin-bottom: 15px;
            font-size: 14px;
            line-height: 1.5;
            color: #444;
        }

        .job-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }

        .job-tag {
            background-color: #f0f4ff;
            color: #0b2239;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }

        .job-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .job-salary {
            font-weight: 600;
            color: #333;
        }

        .job-posted {
            font-size: 13px;
            color: #777;
        }

        .apply-button {
            font-family: 'Poppins', sans-serif;
            background-color: #0b2239;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .apply-button:hover {
            background-color: #007b8f;
        }

        /* Application Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 10;
        }

        .modal-title {
            font-family: 'Poppins';
            font-weight: 600;
            font-size: 18px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
            font-family: 'Roboto', sans-serif;
        }

        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            
        }

        .form-textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            min-height: 100px;
            resize: vertical;
            font-family: 'Roboto';
        }

        .file-input-container {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-input-label {
            background-color: #00bfa6;
            color: #00bfa6;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            display: inline-block;
            transition: background-color 0.2s;
            font-family: 'Courier New', Courier, monospace;
        }

        .file-input-label:hover {
            background-color: #00bfa6;
        }

        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .file-name {
            margin-left: 10px;
            font-size: 14px;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            position: sticky;
            bottom: 0;
            background-color: white;
            z-index: 10;
        }

        .cancel-button {
            background-color: #f0f0f0;
            color: #333;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .cancel-button:hover {
            background-color: #e0e0e0;
        }

        .submit-button {
            background-color: #0b2239;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            font-family: 'Poppins', sans-serif;
        }

        .submit-button:hover {
            background-color: #007b8f;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 30px;
        }

        .page-button {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: white;
            color: #333;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .page-button:hover {
            background-color: #f0f4ff;
            border-color: #4a6cf7;
        }

        .page-button.active {
            background-color: #4a6cf7;
            color: white;
            border-color: #4a6cf7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .search-container {
                flex-direction: column;
            }

            .job-header {
                flex-direction: column;
            }

            .job-logo {
                margin-bottom: 10px;
            }

            .job-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .job-info {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Your Applications</div>
        </div>
        <div class="card-body">
            <?php if (empty($userApplications)): ?>
                <p>You have not applied to any jobs yet.</p>
            <?php else: ?>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Job</th>
                            <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Status</th>
                            <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userApplications as $app): ?>
                            <?php $job = $jobObj->getJobByID($app['job_id']); ?>
                            <tr>
                                <td style="padding:8px; border-bottom:1px solid #f5f5f5;">
                                    <span style="color:#0b2239; font-weight:500;">
                                        <?= htmlspecialchars($job['job_name'] ?? 'Unknown Job') ?>
                                    </span>
                                </td>
                                <td style="padding:8px; border-bottom:1px solid #f5f5f5;">
                                    <?= htmlspecialchars(ucfirst($app['status'])) ?>
                                </td>
                                <td style="padding:8px; border-bottom:1px solid #f5f5f5;">
                                    <?= isset($app['timestamp']) ? htmlspecialchars($app['timestamp']) : '' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">Find Your Next Opportunity</div>
        </div>
        <div class="card-body">
            <form class="search-container" method="get" action="jobs.php">
                <input type="text" class="search-input" name="search" placeholder="Search for jobs, companies, or keywords..." value="<?= htmlspecialchars($search) ?>">
                <button class="search-button" type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="job-list">
        <?php foreach ($jobs as $job): ?>
        <div class="job-card">
            <div class="job-title"><?= htmlspecialchars($job['job_name']) ?></div>
            <p><?= nl2br(htmlspecialchars($job['job_description'])) ?></p>
            <div><strong>Location:</strong> <?= htmlspecialchars($job['location']) ?></div>
            <div><strong>Type:</strong> <?= htmlspecialchars($job['type']) ?></div>
            <div><strong>Salary:</strong> $<?= htmlspecialchars($job['salary']) ?></div>
            
            <button class="apply-button" data-job-id="<?= $job['job_id'] ?>" data-job-title="<?= htmlspecialchars($job['job_name']) ?>">Apply Now</button>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal" id="application-modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">Apply for <span id="modal-job-title"></span></div>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="application-form" action="../back_script/submit_application.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="job-id" name="jobID">
                <div class="form-group"><label class="form-label">Full Name</label><input class="form-input" type="text" name="fullName" required></div>
                <div class="form-group"><label class="form-label">Email</label><input class="form-input" type="email" name="email" required></div>
                <div class="form-group"><label class="form-label">Application Description</label><textarea class="form-textarea" name="applicationDescription" placeholder="Tell us why you're interested in this position..." required></textarea></div>
                <div class="form-group"><label class="form-label">Resume</label><input class="form-input" type="file" name="resume" accept=".pdf,.doc,.docx"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="submit-button" id="submit-application">Submit Application</button>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('application-modal');
    const modalJobTitle = document.getElementById('modal-job-title');
    const jobIdInput = document.getElementById('job-id');
    const applyButtons = document.querySelectorAll('.apply-button');
    const closeModal = document.querySelector('.close-modal');
    const submitButton = document.getElementById('submit-application');

    applyButtons.forEach(button => {
        button.addEventListener('click', () => {
            modal.classList.add('active');
            modalJobTitle.textContent = button.getAttribute('data-job-title');
            jobIdInput.value = button.getAttribute('data-job-id');
        });
    });
    

    closeModal.onclick = () => modal.classList.remove('active');
    submitButton.onclick = () => {
    const form = document.getElementById('application-form');
    if (form.checkValidity()) {
        form.submit(); 
    } else {
        form.reportValidity();
    }
};
</script>
</body>
</html>
