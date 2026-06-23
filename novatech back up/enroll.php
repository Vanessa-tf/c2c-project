<?php
session_start();

// Generate random password
function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

// Initialize session data if not set
if (!isset($_SESSION['enroll_data'])) {
    $_SESSION['enroll_data'] = [];
}
if (!isset($_SESSION['enroll_errors'])) {
    $_SESSION['enroll_errors'] = [];
}

// Get current step from URL or default to 1
$current_step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if ($current_step < 1) $current_step = 1;
if ($current_step > 7) $current_step = 7;

// Get data and errors from session
$data = $_SESSION['enroll_data'];
$errors = $_SESSION['enroll_errors'];

// Clear errors after displaying
unset($_SESSION['enroll_errors']);

// Subjects for dropdown
$subjects = [
    "Mathematics", "Physical Science", "English", "Life Sciences",
    "Geography", "History", "Accounting", "Business Studies",
    "CAT (Computer Applications Technology)", "Economics"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll - NovaTech FET College</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding: 0 15%;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
        }
        .step-circle.completed {
            background: #0d6efd;
            color: white;
        }
        .step-label {
            position: absolute;
            top: 50px;
            white-space: nowrap;
            font-size: 0.8rem;
            text-align: center;
            width: 100px;
            left: -30px;
        }
        .step-content { display: none; }
        .step-content.active { display: block; }
        .btn-nav { margin: 1rem 0; }
        .error { 
            color: #dc3545; 
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
        .upload-box {
            border: 2px dashed #0d6efd;
            padding: 2rem;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
        }
        .required::after {
            content: " *";
            color: #dc3545;
        }
        .spinner-border {
            width: 1.5rem;
            height: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">🎓 Enrollment Process</h1>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <?php for ($i = 1; $i <= 7; $i++): ?>
                <div class="text-center">
                    <div class="step-circle <?= $i <= $current_step ? 'completed' : '' ?>"><?= $i ?></div>
                    <div class="step-label">
                        <?php
                            $labels = ["Account", "Personal", "Academic", "Financing", "Package", "Review", "Done"];
                            echo $labels[$i-1];
                        ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <form id="enrollForm" method="POST" action="process-enrollment.php" enctype="multipart/form-data">
            <input type="hidden" name="current_step" value="<?= $current_step ?>">

            <!-- STEP 1: Account Setup -->
            <div class="step-content <?= $current_step == 1 ? 'active' : '' ?>" id="step1">
                <h3>Step 1: Account Setup</h3>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        Please correct the following errors:
                        <ul>
                            <?php foreach ($errors as $field => $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label required">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number (Optional)</label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" placeholder="0812345678">
                    <small class="form-text text-muted">Format: 0812345678 (10 digits, starts with 0)</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password (System Generated)</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['password'] ?? generatePassword()) ?>" readonly>
                    <small class="form-text text-muted">You can change this later via OTP.</small>
                </div>
                <button type="submit" class="btn btn-primary btn-nav">Next →</button>
            </div>

            <!-- STEP 2: Personal Info -->
            <div class="step-content <?= $current_step == 2 ? 'active' : '' ?>" id="step2">
                <h3>Step 2: Personal Information</h3>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($data['first_name'] ?? '') ?>" required>
                        <?php if (!empty($errors['first_name'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['first_name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Middle Name(s)</label>
                        <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($data['middle_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Surname</label>
                        <input type="text" name="surname" class="form-control" value="<?= htmlspecialchars($data['surname'] ?? '') ?>" required>
                        <?php if (!empty($errors['surname'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['surname']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($data['dob'] ?? '') ?>" required>
                        <?php if (!empty($errors['dob'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['dob']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">ID/Passport Number</label>
                        <input type="text" name="id_number" class="form-control" value="<?= htmlspecialchars($data['id_number'] ?? '') ?>" placeholder="13 digits for SA ID" required>
                        <?php if (!empty($errors['id_number'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['id_number']) ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">South African ID: 13 digits</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select...</option>
                        <option value="Male" <?= ($data['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($data['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= ($data['gender'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                    <?php if (!empty($errors['gender'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['gender']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Home Address</label>
                    <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($data['address'] ?? '') ?></textarea>
                    <?php if (!empty($errors['address'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['address']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="text" name="city" class="form-control" placeholder="City" value="<?= htmlspecialchars($data['city'] ?? '') ?>" required>
                        <?php if (!empty($errors['city'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['city']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <input type="text" name="province" class="form-control" placeholder="Province" value="<?= htmlspecialchars($data['province'] ?? '') ?>" required>
                        <?php if (!empty($errors['province'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['province']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <input type="text" name="postal_code" class="form-control" placeholder="Postal Code" value="<?= htmlspecialchars($data['postal_code'] ?? '') ?>" required>
                        <?php if (!empty($errors['postal_code'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['postal_code']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Nationality</label>
                    <input type="text" name="nationality" class="form-control" value="<?= htmlspecialchars($data['nationality'] ?? '') ?>" required>
                    <?php if (!empty($errors['nationality'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['nationality']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Emergency Contact Name</label>
                    <input type="text" name="emergency_contact_name" class="form-control" value="<?= htmlspecialchars($data['emergency_contact_name'] ?? '') ?>" required>
                    <?php if (!empty($errors['emergency_contact_name'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['emergency_contact_name']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Emergency Contact Phone</label>
                    <input type="tel" name="emergency_contact_phone" class="form-control" value="<?= htmlspecialchars($data['emergency_contact_phone'] ?? '') ?>" placeholder="0812345678" required>
                    <?php if (!empty($errors['emergency_contact_phone'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['emergency_contact_phone']) ?></div>
                    <?php endif; ?>
                    <small class="form-text text-muted">Format: 0812345678 (10 digits, starts with 0)</small>
                </div>
                <div class="btn-nav">
                    <a href="?step=1" class="btn btn-secondary">← Back</a>
                    <button type="submit" class="btn btn-primary">Next →</button>
                </div>
            </div>

            <!-- STEP 3: Academic Info -->
            <div class="step-content <?= $current_step == 3 ? 'active' : '' ?>" id="step3">
                <h3>Step 3: Academic Information</h3>
                <div class="mb-3">
                    <label class="form-label required">Subjects to Rewrite</label>
                    <div>
                        <?php foreach ($subjects as $subject): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="subjects[]" value="<?= $subject ?>"
                                    <?= in_array($subject, $data['subjects'] ?? []) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $subject ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($errors['subjects'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['subjects']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Previous School/Exam Board</label>
                    <input type="text" name="previous_school" class="form-control" value="<?= htmlspecialchars($data['previous_school'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Year of Last Attempt</label>
                    <select name="last_exam_year" class="form-select">
                        <option value="">Select Year</option>
                        <?php for ($y = date('Y'); $y >= 2000; $y--): ?>
                            <option value="<?= $y ?>" <?= ($data['last_exam_year'] ?? '') == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload ID/Proof of Last Exam Results (PDF/Image)</label>
                    <div class="upload-box" onclick="document.getElementById('proof_upload').click()">
                        <i class="fas fa-cloud-upload-alt fa-2x"></i><br>
                        Click to Upload
                    </div>
                    <input type="file" id="proof_upload" name="proof_upload" accept=".pdf,.jpg,.jpeg,.png" style="display:none" onchange="showFileName(this)">
                    <div id="file-name" class="mt-2"></div>
                    <?php if (!empty($data['proof_upload'])): ?>
                        <div class="alert alert-success mt-2">Previously uploaded: <?= basename($data['proof_upload']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="btn-nav">
                    <a href="?step=2" class="btn btn-secondary">← Back</a>
                    <button type="submit" class="btn btn-primary">Next →</button>
                </div>
            </div>

            <!-- STEP 4: Financing -->
            <div class="step-content <?= $current_step == 4 ? 'active' : '' ?>" id="step4">
                <h3>Step 4: Financing Details</h3>
                <div class="mb-3">
                    <label class="form-label required">Who will finance your studies?</label>
                    <div>
                        <?php $financier = $data['financier_type'] ?? ''; ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="financier_type" value="Self" id="fin_self" <?= $financier=='Self'?'checked':'' ?> required>
                            <label class="form-check-label" for="fin_self">Self</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="financier_type" value="Parent/Guardian" id="fin_parent" <?= $financier=='Parent/Guardian'?'checked':'' ?>>
                            <label class="form-check-label" for="fin_parent">Parent/Guardian</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="financier_type" value="Sponsor/Other" id="fin_sponsor" <?= $financier=='Sponsor/Other'?'checked':'' ?>>
                            <label class="form-check-label" for="fin_sponsor">Sponsor/Other</label>
                        </div>
                    </div>
                    <?php if (!empty($errors['financier_type'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['financier_type']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Self Financing -->
                <div id="selfFinancing" class="financing-section <?= $financier=='Self'?'d-block':'d-none' ?>">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="confirm_age" id="confirm_age" value="1" <?= ($data['confirm_age'] ?? '') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="confirm_age">I am 18 years or older *</label>
                        <?php if (!empty($errors['confirm_age'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['confirm_age']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Occupation/Income Source</label>
                        <input type="text" name="occupation" class="form-control" value="<?= htmlspecialchars($data['occupation'] ?? '') ?>">
                        <?php if (!empty($errors['occupation'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['occupation']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Select...</option>
                            <option value="Card" <?= ($data['payment_method'] ?? '')=='Card'?'selected':'' ?>>Card</option>
                            <option value="EFT" <?= ($data['payment_method'] ?? '')=='EFT'?'selected':'' ?>>EFT</option>
                        </select>
                        <?php if (!empty($errors['payment_method'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['payment_method']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Parent Financing -->
                <div id="parentFinancing" class="financing-section <?= $financier=='Parent/Guardian'?'d-block':'d-none' ?>">
                    <div class="mb-3">
                        <label class="form-label required">Parent/Guardian Full Name</label>
                        <input type="text" name="financier_name" class="form-control" value="<?= htmlspecialchars($data['financier_name'] ?? '') ?>">
                        <?php if (!empty($errors['financier_name'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['financier_name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Relationship to Student</label>
                        <input type="text" name="financier_relationship" class="form-control" value="<?= htmlspecialchars($data['financier_relationship'] ?? '') ?>">
                        <?php if (!empty($errors['financier_relationship'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['financier_relationship']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Parent/Guardian ID/Passport</label>
                        <input type="text" name="financier_id" class="form-control" value="<?= htmlspecialchars($data['financier_id'] ?? '') ?>" placeholder="13 digits for SA ID">
                        <?php if (!empty($errors['financier_id'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['financier_id']) ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">South African ID: 13 digits</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Parent/Guardian Phone</label>
                        <input type="tel" name="financier_phone" class="form-control" value="<?= htmlspecialchars($data['financier_phone'] ?? '') ?>" placeholder="0812345678">
                        <?php if (!empty($errors['financier_phone'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['financier_phone']) ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Format: 0812345678 (10 digits, starts with 0)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Parent/Guardian Email</label>
                        <input type="email" name="financier_email" class="form-control" value="<?= htmlspecialchars($data['financier_email'] ?? '') ?>">
                        <?php if (!empty($errors['financier_email'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['financier_email']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Parent/Guardian Address</label>
                        <textarea name="financier_address" class="form-control" rows="2"><?= htmlspecialchars($data['financier_address'] ?? '') ?></textarea>
                        <?php if (!empty($errors['financier_address'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['financier_address']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Select...</option>
                            <option value="Card" <?= ($data['payment_method'] ?? '')=='Card'?'selected':'' ?>>Card</option>
                            <option value="EFT" <?= ($data['payment_method'] ?? '')=='EFT'?'selected':'' ?>>EFT</option>
                        </select>
                        <?php if (!empty($errors['payment_method'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['payment_method']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sponsor Financing -->
                <div id="sponsorFinancing" class="financing-section <?= $financier=='Sponsor/Other'?'d-block':'d-none' ?>">
                    <div class="mb-3">
                        <label class="form-label required">Sponsor/Organization Name</label>
                        <input type="text" name="sponsor_org_name" class="form-control" value="<?= htmlspecialchars($data['sponsor_org_name'] ?? '') ?>">
                        <?php if (!empty($errors['sponsor_org_name'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['sponsor_org_name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Contact Person Name</label>
                        <input type="text" name="sponsor_contact_person" class="form-control" value="<?= htmlspecialchars($data['sponsor_contact_person'] ?? '') ?>">
                        <?php if (!empty($errors['sponsor_contact_person'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['sponsor_contact_person']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Sponsor Email</label>
                        <input type="email" name="sponsor_email" class="form-control" value="<?= htmlspecialchars($data['sponsor_email'] ?? '') ?>">
                        <?php if (!empty($errors['sponsor_email'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['sponsor_email']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Sponsor Phone</label>
                        <input type="tel" name="sponsor_phone" class="form-control" value="<?= htmlspecialchars($data['sponsor_phone'] ?? '') ?>" placeholder="0812345678">
                        <?php if (!empty($errors['sponsor_phone'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['sponsor_phone']) ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Format: 0812345678 (10 digits, starts with 0)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Organization Address</label>
                        <textarea name="sponsor_address" class="form-control" rows="2"><?= htmlspecialchars($data['sponsor_address'] ?? '') ?></textarea>
                        <?php if (!empty($errors['sponsor_address'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['sponsor_address']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Sponsorship Letter</label>
                        <div class="upload-box" onclick="document.getElementById('sponsor_letter').click()">
                            <i class="fas fa-cloud-upload-alt fa-2x"></i><br>Click to Upload
                        </div>
                        <input type="file" id="sponsor_letter" name="sponsor_letter" accept=".pdf,.jpg,.jpeg,.png" style="display:none" onchange="showFileName(this, 'sponsor-file')">
                        <div id="sponsor-file" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Select...</option>
                            <option value="Card" <?= ($data['payment_method'] ?? '')=='Card'?'selected':'' ?>>Card</option>
                            <option value="EFT" <?= ($data['payment_method'] ?? '')=='EFT'?'selected':'' ?>>EFT</option>
                        </select>
                        <?php if (!empty($errors['payment_method'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['payment_method']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="btn-nav">
                    <a href="?step=3" class="btn btn-secondary">← Back</a>
                    <button type="submit" class="btn btn-primary">Next →</button>
                </div>
            </div>

            <!-- STEP 5: Package & Payment -->
            <div class="step-content <?= $current_step == 5 ? 'active' : '' ?>" id="step5">
                <h3>Step 5: Package Selection & Payment</h3>
                <div class="row">
                    <?php
                    $packages = [
                        ['name'=>'Basic', 'price'=>'R299/month', 'features'=>['Past exam papers','5 recorded lessons','Basic tracking']],
                        ['name'=>'Standard', 'price'=>'R499/month', 'features'=>['All Basic','10 recorded + 2 live','Advanced tracking']],
                        ['name'=>'Premium', 'price'=>'R799/month', 'features'=>['All Standard','Unlimited lessons','Tutor support','Community']]
                    ];
                    foreach ($packages as $pkg):
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="card <?= ($data['package_selected'] ?? '') == $pkg['name'] ? 'border-primary' : '' ?>">
                            <div class="card-header text-center">
                                <h5><?= $pkg['name'] ?></h5>
                                <h4><?= $pkg['price'] ?></h4>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <?php foreach ($pkg['features'] as $feature): ?>
                                        <li><?= $feature ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="package_selected" value="<?= $pkg['name'] ?>"
                                        id="pkg_<?= strtolower($pkg['name']) ?>" <?= ($data['package_selected'] ?? '') == $pkg['name'] ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="pkg_<?= strtolower($pkg['name']) ?>">Select</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($errors['package_selected'])): ?>
                    <div class="error text-center mb-3"><?= htmlspecialchars($errors['package_selected']) ?></div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <h5 class="required">Payment Method</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method_real" id="card_payment" value="Card" <?= ($data['payment_method_real'] ?? '') == 'Card' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="card_payment">
                            <i class="fas fa-credit-card"></i> Credit/Debit Card
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method_real" id="eft_payment" value="EFT" <?= ($data['payment_method_real'] ?? '') == 'EFT' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="eft_payment">
                            <i class="fas fa-university"></i> EFT/Bank Transfer
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method_real" id="cash_payment" value="Cash" <?= ($data['payment_method_real'] ?? '') == 'Cash' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="cash_payment">
                            <i class="fas fa-money-bill-wave"></i> Cash Deposit
                        </label>
                    </div>
                    <?php if (!empty($errors['payment_method_real'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['payment_method_real']) ?></div>
                    <?php endif; ?>
                </div>

                <div id="cardForm" class="mt-3 <?= ($data['payment_method_real'] ?? '') == 'Card' ? 'd-block' : 'd-none' ?>">
                    <h6>Enter Card Details</h6>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <input type="text" class="form-control" placeholder="Card Number" id="cardNumber" maxlength="19">
                        </div>
                        <div class="col-md-4 mb-3">
                            <input type="text" class="form-control" placeholder="CVV" id="cardCVV" maxlength="4">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" placeholder="Expiry MM/YY" id="cardExpiry" maxlength="5">
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" placeholder="Cardholder Name" id="cardName">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-success" id="payButton">💳 Process Payment</button>
                    <div id="paymentResult" class="mt-3"></div>
                    <div id="paymentLoader" class="d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Processing...</span>
                        </div>
                        <span>Processing your payment...</span>
                    </div>
                </div>
                <div class="btn-nav">
                    <a href="?step=4" class="btn btn-secondary">← Back</a>
                    <button type="submit" class="btn btn-primary">Next →</button>
                </div>
            </div>

            <!-- STEP 6: Review -->
            <div class="step-content <?= $current_step == 6 ? 'active' : '' ?>" id="step6">
                <h3>Step 6: Review & Confirmation</h3>
                <div class="alert alert-info">
                    <h5>Student Information</h5>
                    <p><strong>Name:</strong> <?= htmlspecialchars(($data['first_name'] ?? '') . ' ' . ($data['middle_name'] ?? '') . ' ' . ($data['surname'] ?? '')) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($data['email'] ?? '') ?></p>
                    <p><strong>Subjects:</strong> <?= implode(', ', $data['subjects'] ?? []) ?></p>
                    <hr>
                    <h5>Financier: <?= htmlspecialchars($data['financier_type'] ?? '') ?></h5>
                    <?php if ($data['financier_type'] ?? '' == 'Parent/Guardian'): ?>
                        <p><strong>Name:</strong> <?= htmlspecialchars($data['financier_name'] ?? '') ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($data['financier_email'] ?? '') ?></p>
                    <?php endif; ?>
                    <hr>
                    <h5>Package: <?= htmlspecialchars($data['package_selected'] ?? '') ?></h5>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="confirm_details" name="confirm_details" value="1" <?= ($data['confirm_details'] ?? '') ? 'checked' : '' ?> required>
                    <label class="form-check-label" for="confirm_details">I confirm that all details are correct and I agree to the cancellation rules & terms.</label>
                    <?php if (!empty($errors['confirm_details'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['confirm_details']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="btn-nav">
                    <a href="?step=5" class="btn btn-secondary">← Back</a>
                    <button type="submit" class="btn btn-primary">Submit Enrollment</button>
                </div>
            </div>

            <!-- STEP 7: OTP Verification -->
            <div class="step-content <?= $current_step == 7 ? 'active' : '' ?>" id="step7">
                <h3>Step 7: Verify Your Email</h3>
                <p>We sent a 6-digit OTP to: <strong><?= htmlspecialchars($data['email'] ?? 'your email') ?></strong></p>
                <p><em>(In real system, we'd send via email. Here, use any 6 digits!)</em></p>
                <div class="mb-3">
                    <label class="form-label">Enter OTP</label>
                    <input type="text" name="otp" class="form-control" maxlength="6" required>
                    <?php if (!empty($errors['otp'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['otp']) ?></div>
                    <?php endif; ?>
                </div>
                <button type="submit" name="action" value="verify_otp" class="btn btn-success">Verify & Complete Enrollment</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        // Show file name after upload
        function showFileName(input, containerId = 'file-name') {
            if (input.files.length > 0) {
                document.getElementById(containerId).innerText = 'Selected: ' + input.files[0].name;
            }
        }

        // Toggle financing sections
        document.querySelectorAll('input[name="financier_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.financing-section').forEach(sec => sec.classList.add('d-none'));
                if (this.value === 'Self') {
                    document.getElementById('selfFinancing').classList.remove('d-none');
                } else if (this.value === 'Parent/Guardian') {
                    document.getElementById('parentFinancing').classList.remove('d-none');
                } else if (this.value === 'Sponsor/Other') {
                    document.getElementById('sponsorFinancing').classList.remove('d-none');
                }
            });
        });

        // Show card form when card payment is selected
        document.getElementById('card_payment')?.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('cardForm').classList.remove('d-none');
            }
        });

        // Hide card form when other payment methods are selected
        document.getElementById('eft_payment')?.addEventListener('change', function() {
            document.getElementById('cardForm').classList.add('d-none');
        });
        document.getElementById('cash_payment')?.addEventListener('change', function() {
            document.getElementById('cardForm').classList.add('d-none');
        });

        // Fake payment processing
        document.getElementById('payButton')?.addEventListener('click', function() {
            const paymentMethod = document.querySelector('input[name="payment_method_real"]:checked');
            if (!paymentMethod) {
                alert('Please select a payment method!');
                return;
            }
            
            // Validate card details if card payment
            if (paymentMethod.value === 'Card') {
                const cardNumber = document.getElementById('cardNumber').value;
                const cardCVV = document.getElementById('cardCVV').value;
                const cardExpiry = document.getElementById('cardExpiry').value;
                const cardName = document.getElementById('cardName').value;
                
                if (!cardNumber || cardNumber.replace(/\s/g, '').length < 16) {
                    alert('Please enter a valid card number (at least 16 digits)!');
                    return;
                }
                if (!cardCVV || cardCVV.length < 3) {
                    alert('Please enter a valid CVV (3-4 digits)!');
                    return;
                }
                if (!cardExpiry || !/^\d{2}\/\d{2}$/.test(cardExpiry)) {
                    alert('Please enter expiry date as MM/YY (e.g., 12/25)!');
                    return;
                }
                if (!cardName) {
                    alert('Please enter cardholder name!');
                    return;
                }
            }
            
            // Show loading spinner
            document.getElementById('payButton').disabled = true;
            document.getElementById('paymentLoader').classList.remove('d-none');
            document.getElementById('paymentResult').innerHTML = '';
            
            // Simulate payment processing
            setTimeout(function() {
                const success = Math.random() > 0.1; // 90% success rate
                
                if (success) {
                    document.getElementById('paymentResult').innerHTML = `
                        <div class="alert alert-success">
                            <h4>✅ Payment Successful!</h4>
                            <p>Your payment has been processed.</p>
                            <p>You will now proceed to the final verification step.</p>
                        </div>
                    `;
                    setTimeout(function() {
                        document.querySelector('form').submit();
                    }, 2000);
                } else {
                    document.getElementById('paymentResult').innerHTML = `
                        <div class="alert alert-danger">
                            <h4>❌ Payment Failed!</h4>
                            <p>There was an error processing your payment. Please try again.</p>
                        </div>
                    `;
                    document.getElementById('payButton').disabled = false;
                }
                document.getElementById('paymentLoader').classList.add('d-none');
            }, 2000 + Math.random() * 3000);
        });
    </script>
</body>
</html>