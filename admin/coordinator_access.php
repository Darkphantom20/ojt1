<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Coordinator Access';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../dbconnection.php';

$coordinatorAccounts = [];
$result = $conn->query("SELECT full_name, email, department, access_code, status, created_at FROM coordinator_accounts ORDER BY created_at DESC LIMIT 10");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $coordinatorAccounts[] = $row;
    }
}

$createdCode = isset($_GET['created']) && $_GET['created'] == 1 ? ($_GET['code'] ?? '') : '';
?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <div class="preloader flex-column justify-content-center align-items-center" style="background: linear-gradient(to bottom, blue, yellow);">
    <img class="animation__shake" src="../assets/img/users/OIP.webp" alt="Preloader" height="150" width="150" style="border-radius: 50%;">
  </div>

  <?php include __DIR__ . '/../includes/navbar_admin.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <!-- Coordinator setup guide popup -->
  <div class="modal fade" id="coordinatorGuideModal" tabindex="-1" role="dialog" aria-labelledby="coordinatorGuideLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="coordinatorGuideLabel">Coordinator Access Guide</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="font-weight-bold">Step-by-step process for creating a new coordinator account and generating access codes automatically:</p>
          <ol class="mb-0">
            <li>Fill in the form with full name, email, department, and temporary password.</li>
            <li>Click <strong>Create Account</strong>.</li>
            <li>The system saves the account and generates a unique coordinator access code.</li>
            <li>Copy the displayed access code from the success panel and give it to the coordinator.</li>
            <li>Coordinator logs in using their email/password and the provided access code.</li>
          </ol>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
        </div>
      </div>
    </div>
  </div>

  <div class="content-wrapper">
    <div class="content-header px-3 py-2" style="background: rgba(255,255,255,0.48); backdrop-filter: blur(7px); border-bottom: 1px solid rgba(0, 0, 0, 0.08);">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h1 class="h3 mb-1 font-weight-bold text-dark">Coordinator Access</h1>
            <p class="mb-0 text-secondary">Add or update coordinator accounts, then share the generated access code securely.</p>
          </div>
          <div class="col-md-4 text-md-right">
            <span class="badge badge-pill badge-info px-3 py-2" style="font-size: 0.9rem;">Real-time management</span>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <style>
          /* Enhance alert styles */
          .alert-modern {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 14px 20px;
            font-weight: 500;
            border-left: 5px solid transparent;
            animation: slideDown 0.4s ease forwards;
          }
          .alert-modern.alert-success {
            border-left-color: #28a745;
            background-color: #e9f7ec;
            color: #1e7e34;
          }
          .alert-modern.alert-danger {
            border-left-color: #dc3545;
            background-color: #fde8ec;
            color: #a71d2a;
          }
          .alert-modern.alert-warning {
            border-left-color: #ffc107;
            background-color: #fff8e1;
            color: #856404;
          }
          .alert-modern.alert-info {
            border-left-color: #17a2b8;
            background-color: #e3f4f8;
            color: #0c6b7a;
          }

          @keyframes slideDown {
            0% { opacity: 0; transform: translateY(-15px); }
            100% { opacity: 1; transform: translateY(0); }
          }

          .alert-fade-out {
            animation: fadeOut 0.5s ease forwards;
          }
          @keyframes fadeOut {
            0% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
          }

          .coordinator-access-row .card.card-success {
            min-height: 450px;
          }
          .coordinator-access-row .card.card-success .card-body {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            overflow-y: auto;
            max-height: 360px;
            gap: 1rem;
          }
          .coordinator-access-row .card.card-success .code-box {
            flex: 1;
            border: 1px solid #28a745;
            border-radius: 0.35rem;
            background: #f8fff8;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
          }
          .coordinator-access-row .card.card-success .code-box h5 {
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
          }
          .coordinator-access-row .card.card-success .code-box p {
            margin-bottom: 0.5rem;
            font-family: monospace;
            font-size: 1.05rem;
            word-break: break-all;
          }
          .coordinator-access-row .card.card-success .card-footer {
            position: sticky;
            bottom: 0;
            z-index: 1;
            background: #fff;
          }
        </style>
        <div class="row coordinator-access-row">

          <div class="col-md-6">
            <div class="card card-primary">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-user-plus"></i> Create New Coordinator Account</h3>
                <button type="button" id="coordinatorGuideBtn" class="btn btn-info btn-sm" aria-label="Coordinator Access Guide">
                  <i class="fas fa-question-circle"></i>
                </button>
              </div>
              <form id="createCoordinatorForm" action="process_create_account.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <div class="card-body">
                  
                  <!-- ============================================= -->
                  <!-- MODERN ALERTS (with auto‑dismiss for success) -->
                  <!-- ============================================= -->
                  <?php if (isset($_GET['created']) && $_GET['created'] == 1): ?>
                      <?php if (isset($_GET['mail']) && $_GET['mail'] === 'ok'): ?>
                          <div class="alert alert-modern alert-success alert-dismissible fade show" role="alert" id="successAlert">
                              <i class="fas fa-check-circle mr-2"></i> 
                              <strong>Account Created!</strong> The access code has been sent to the coordinator’s email.
                              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                              </button>
                          </div>
                      <?php elseif (isset($_GET['mail']) && $_GET['mail'] === 'failed'): ?>
                          <div class="alert alert-modern alert-warning alert-dismissible fade show" role="alert">
                              <i class="fas fa-exclamation-triangle mr-2"></i>
                              <strong>Account Created, but Email Failed.</strong> 
                              The access code is displayed below – please copy and send it manually.
                              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                              </button>
                          </div>
                      <?php else: ?>
                          <div class="alert alert-modern alert-success alert-dismissible fade show" role="alert" id="successAlert">
                              <i class="fas fa-check-circle mr-2"></i> 
                              <strong>Success!</strong> Coordinator account created.
                              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                              </button>
                          </div>
                      <?php endif; ?>
                  <?php endif; ?>

                  <!-- Error alerts (stay until dismissed) -->
                  <?php if (isset($_GET['error'])): ?>
                      <div class="alert alert-modern alert-danger alert-dismissible fade show" role="alert">
                          <i class="fas fa-times-circle mr-2"></i>
                          <strong>Error:</strong> <?= htmlspecialchars($_GET['error'], ENT_QUOTES) ?>
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                          </button>
                      </div>
                  <?php endif; ?>

                  <div class="alert alert-modern alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle mr-2"></i>
                    Add coordinator accounts here. A unique access code is generated automatically and will be sent to the coordinator’s email.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>

                  <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" class="form-control" id="fullName" name="full_name" placeholder="Enter full name" required>
                  </div>
                  <div class="form-group">
                    <label for="emailAddress">Email Address</label>
                    <input type="email" class="form-control" id="emailAddress" name="email" placeholder="Enter email" required>
                  </div>
                  <div class="form-group">
                    <label for="collegeSelect">College</label>
                    <select class="form-control" id="collegeSelect" name="department" required>
                      <option value="" selected disabled hidden>Select College</option>
                      <option value="Education">Education</option>
                      <option value="College of Arts">College of Arts</option>
                      <option value="College of Agriculture & Forestry">College of Agriculture & Forestry</option>
                      <option value="College of Business & Management">College of Business & Management</option>
                      <option value="College of Computing Studies">College of Computing Studies</option>
                      <option value="College of Criminology">College of Criminology</option>
                    </select>
                  </div>
                  <input type="hidden" name="role" value="coordinator">
                  <div class="form-group">
                    <label for="userPassword">Temporary Password</label>
                    <input type="password" class="form-control" id="userPassword" name="password" placeholder="Password" required>
                  </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary float-right">Create Account</button>
                </div>
              </form>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-qrcode"></i> Coordinator Access Codes</h3>
              </div>
              <div class="card-body">
                <p class="text-muted">Codes are generated automatically upon account creation. Copy the code from confirmation message below.</p>
                <div class="text-center mb-4">
                  <h4 class="text-success">Ready on account creation</h4>
                </div>
                <?php if (!empty($createdCode)): ?>
                  <div class="code-box">
                    <h5>Coordinator Access Code</h5>
                    <p id="generatedCodeText" class="text-monospace font-weight-bold text-dark" style="cursor:pointer; user-select: all;"><?= htmlspecialchars($createdCode, ENT_QUOTES) ?></p>
                    <small id="copyHint" class="text-muted">Click code to copy</small>
                  </div>
                <?php else: ?>
                  <div class="code-box">
                    <h5>No code yet</h5>
                    <p class="text-muted">Create an account to generate the access code.</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
  <?php include __DIR__ . '/../includes/script.php'; ?>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // ----- Auto-dismiss success alerts after 5 seconds -----
    const successAlert = document.getElementById('successAlert');
    if (successAlert) {
      setTimeout(function() {
        successAlert.classList.add('alert-fade-out');
        // Remove from DOM after animation
        setTimeout(function() {
          if (successAlert.parentNode) {
            successAlert.parentNode.removeChild(successAlert);
          }
        }, 500);
      }, 5000);
    }

    // ----- Copy access code on click -----
    const generatedCodeNode = document.getElementById('generatedCodeText');
    const copyHintNode = document.getElementById('copyHint');

    if (generatedCodeNode) {
      generatedCodeNode.addEventListener('click', function() {
        const code = generatedCodeNode.textContent.trim();
        if (!code) return;

        navigator.clipboard.writeText(code).then(function() {
          if (copyHintNode) {
            copyHintNode.textContent = 'Copied!';
            setTimeout(function() {
              copyHintNode.textContent = 'Click code to copy';
            }, 3000);
          }
        }).catch(function() {
          if (copyHintNode) {
            copyHintNode.textContent = 'Unable to copy automatically; please copy manually.';
          }
        });
      });
    }

    // ----- Show guide modal -----
    var guideBtn = document.getElementById('coordinatorGuideBtn');
    if (guideBtn) {
      guideBtn.addEventListener('click', function () {
        $('#coordinatorGuideModal').modal('show');
      });
    }

    // ----- Match card heights -----
    var createCard = document.querySelector('.coordinator-access-row .card-primary');
    var codesCard = document.querySelector('.coordinator-access-row .card-success');
    if (createCard && codesCard) {
      var matchHeight = function() {
        var targetHeight = createCard.getBoundingClientRect().height;
        codesCard.style.minHeight = targetHeight + 'px';
      };
      matchHeight();
      window.addEventListener('resize', matchHeight);
    }
  });
</script>
</body>
</html>