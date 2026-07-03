<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Coordinator Directory';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../dbconnection.php';

require_once __DIR__ . '/../dbconnection.php';

$coordinatorAccounts = [];
$coordinatorResult = $conn->query("SELECT id, full_name, email, department, access_code, status, created_at FROM coordinator_accounts ORDER BY full_name ASC, created_at ASC");
if ($coordinatorResult) {
    while ($row = $coordinatorResult->fetch_assoc()) {
        $coordinatorAccounts[] = $row;
    }
}


$departmentCounts = [];
foreach ($coordinatorAccounts as $acct) {
    $dept = $acct['department'] ?? 'General';
    if (!isset($departmentCounts[$dept])) {
        $departmentCounts[$dept] = 0;
    }
    $departmentCounts[$dept]++;
}
?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <div class="preloader flex-column justify-content-center align-items-center" style="background: linear-gradient(to bottom, blue, yellow);">
    <img class="animation__shake" src="../assets/img/users/OIP.webp" alt="Preloader" height="150" width="150" style="border-radius: 50%;">
  </div>

  <?php include __DIR__ . '/../includes/navbar_admin.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Coordinator Directory</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
              <li class="breadcrumb-item active">Coordinator Directory</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <style>
          #coordinatorAccountsTable tbody tr {
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
          }
          #coordinatorAccountsTable tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.08);
            background-color: rgba(255, 255, 255, 0.95);
          }
          #coordinatorAccountsTable tbody tr.table-primary {
            animation: glow 1.4s ease-in-out infinite alternate;
          }
          @keyframes glow {
            from { box-shadow: 0 0 0 rgba(40, 167, 69, 0.3); }
            to { box-shadow: 0 0 20px rgba(40, 167, 69, 0.4); }
          }
          .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: none;
            border-radius: 4px;
            background: #f4f6f9;
          }
          .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #28a745;
            color: #fff !important;
          }
        </style>
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Coordinator List</h3>
          </div>
          <div class="card-body">
            <?php if (!empty($_GET['success'])): ?>
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <i class="icon fas fa-check"></i> <?= htmlspecialchars($_GET['success'], ENT_QUOTES) ?>
              </div>
            <?php endif; ?>
            <?php if (!empty($_GET['error'])): ?>
              <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <i class="icon fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_GET['error'], ENT_QUOTES) ?>
              </div>
            <?php endif; ?>

            <div class="form-inline mb-3">
              <label for="departmentFilter" class="mr-2">Filter by Department</label>
              <select id="departmentFilter" class="form-control">
                <option value="all">All</option>
                <?php foreach ($departmentCounts as $dept => $count): ?>
                  <option value="<?= htmlspecialchars($dept, ENT_QUOTES) ?>"><?= htmlspecialchars($dept, ENT_QUOTES) ?> (<?= intval($count) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
              <table id="coordinatorAccountsTable" class="table table-striped table-hover table-bordered table-sm" style="min-width: 900px;">
                <thead class="thead-light">
                  <tr>
                    <th>ID</th>
                    <th>Coordinator</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Access Code</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
              <tbody>
                <?php if (!empty($coordinatorAccounts)): ?>
                  <?php foreach ($coordinatorAccounts as $coord): ?>
                    <tr>
                      <td><?= intval($coord['id']) ?></td>
                      <td><?= htmlspecialchars($coord['full_name'], ENT_QUOTES) ?></td>
                      <td><?= htmlspecialchars($coord['email'], ENT_QUOTES) ?></td>
                      <td><?= htmlspecialchars($coord['department'], ENT_QUOTES) ?></td>
                      <td class="text-monospace"><?= htmlspecialchars($coord['access_code'] ?? '', ENT_QUOTES) ?></td>
                      <td>
                        <?php if ($coord['status'] === 'active'): ?>
                          <span class="badge badge-success d-inline-flex align-items-center" style="font-size:0.85rem; padding:0.45rem 0.65rem;">
                            <i class="fas fa-check-circle fa-2x mr-1"></i>
                            Active
                          </span>
                        <?php elseif ($coord['status'] === 'unused'): ?>
                          <span class="badge badge-warning d-inline-flex align-items-center" style="font-size:0.85rem; padding:0.45rem 0.65rem;">
                            <i class="fas fa-hourglass-half fa-2x mr-1"></i>
                            Unused
                          </span>
                        <?php else: ?>
                          <span class="badge badge-secondary d-inline-flex align-items-center" style="font-size:0.85rem; padding:0.45rem 0.65rem;">
                            <i class="fas fa-user-slash fa-2x mr-1"></i>
                            <?= htmlspecialchars($coord['status'], ENT_QUOTES) ?>
                          </span>
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars(date('M j, Y H:i', strtotime($coord['created_at'])), ENT_QUOTES) ?></td>
                      <td class="text-center">
                        <a href="process_faculty.php?action=delete&id=<?= intval($coord['id']) ?>"
                           class="btn btn-outline-danger btn-sm"
                           onclick="return confirm('Delete this coordinator account? This action cannot be undone.');"
                           title="Delete Coordinator">
                          <i class="fas fa-trash-alt"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center">No coordinators found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
            </div>
          </div>
        </div>
      </div>


          </div>
        </div>
      </div>

      <!-- Add/Edit Coordinator Modal -->
      <div class="modal fade" id="facultyModal" tabindex="-1" role="dialog" aria-labelledby="facultyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <form id="facultyForm" action="process_faculty.php" method="POST">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
              <div class="modal-header">
                <h5 class="modal-title" id="facultyModalLabel">Add Coordinator</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <input type="hidden" id="facultyId" name="id" value="">
                <input type="hidden" id="facultyAction" name="action" value="create">

                <div class="form-group">
                  <label for="facultyName">Full Name</label>
                  <input type="text" class="form-control" id="facultyName" name="full_name" required>
                </div>
                <div class="form-group">
                  <label for="facultyEmail">Email</label>
                  <input type="email" class="form-control" id="facultyEmail" name="email" required>
                </div>
                <div class="form-group">
                  <label for="facultyDepartment">Department</label>
                  <input type="text" class="form-control" id="facultyDepartment" name="department" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="facultySubmitBtn">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </section>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
  <?php include __DIR__ . '/../includes/script.php'; ?>
</div>
<script>
  $(document).ready(function () {
    if ($.fn.DataTable) {
      $('#coordinatorAccountsTable').DataTable({
        pageLength: 12,
        lengthChange: true,
        responsive: true,
        autoWidth: false,
        order: [[1, 'asc']],
        columnDefs: [
          { targets: [0, 4, 5], className: 'text-center' },
          { targets: 5, type: 'date' }
        ],
        language: {
          search: 'Quick Search:',
          paginate: {
            next: '<i class="fas fa-chevron-right"></i>',
            previous: '<i class="fas fa-chevron-left"></i>'
          }
        },
        drawCallback: function () {
          $('#coordinatorAccountsTable tbody tr').css('opacity', 0).animate({ opacity: 1 }, 320);
        }
      });
    }
  });

  function openEditFaculty(id, fullName, email, department) {
    document.getElementById('facultyId').value = id;
    document.getElementById('facultyAction').value = 'edit';
    document.getElementById('facultyName').value = fullName;
    document.getElementById('facultyEmail').value = email;
    document.getElementById('facultyDepartment').value = department;
    document.getElementById('facultySubmitBtn').textContent = 'Update';
    $('#facultyModal').modal('show');
  }

  $('#facultyModal').on('hidden.bs.modal', function () {
    document.getElementById('facultyModalLabel').textContent = 'Add Coordinator';
    document.getElementById('facultyId').value = '';
    document.getElementById('facultyAction').value = 'create';
    document.getElementById('facultyForm').reset();
    document.getElementById('facultySubmitBtn').textContent = 'Save';
  });

  
  document.getElementById('departmentFilter')?.addEventListener('change', function () {
    var selected = this.value;
    var rows = document.querySelectorAll('#coordinatorAccountsTable tbody tr');
    rows.forEach(function (row) {
      var dept = row.children[3] ? row.children[3].textContent.trim() : '';
      row.style.display = (selected === 'all' || dept === selected) ? '' : 'none';
    });
  });
</script>
</body>
</html>