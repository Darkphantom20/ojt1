<?php
session_start();
ob_start();
$pageTitle = 'Daily Documentary';

$uploadMessage = '';
$frontPageAttachment = '';
$frontPageAttachmentName = '';

$cleanFrontPageAttachmentName = function ($name) {
    $baseName = basename($name);
    $baseName = preg_replace('/^front_[^_]+_/', '', $baseName);

    $extension = '';
    $nameWithoutExtension = $baseName;

    if (($dotPosition = strrpos($baseName, '.')) !== false) {
        $extension = substr($baseName, $dotPosition);
        $nameWithoutExtension = substr($baseName, 0, $dotPosition);
    }

    if (strlen($nameWithoutExtension) > 20) {
        $nameWithoutExtension = substr($nameWithoutExtension, 0, 17) . '...';
    }

    return $nameWithoutExtension . $extension;
};

if (file_exists(__DIR__ . '/uploads/front_page_attachment.txt')) {
    $frontPageAttachment = trim(file_get_contents(__DIR__ . '/uploads/front_page_attachment.txt'));
    $frontPageAttachmentName = $cleanFrontPageAttachmentName($frontPageAttachment);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['front_page_doc'])) {
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowedExtensions = ['doc', 'docx'];
    $allowedMimeTypes = [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    $file = $_FILES['front_page_doc'];
    if ($file['error'] === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $mimeType = '';

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mimeType = (string) finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
        }

        if (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
            $uploadMessage = 'Only .doc or .docx Word documents are allowed.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $uploadMessage = 'The file is too large. Please upload a Word document smaller than 5MB.';
        } else {
            $safeName = uniqid('front_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $destination = $uploadDir . '/' . $safeName;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                file_put_contents($uploadDir . '/front_page_attachment.txt', $destination);
                $frontPageAttachment = $destination;
                $frontPageAttachmentName = $cleanFrontPageAttachmentName($destination);
                $uploadMessage = 'Front page document attached successfully.';
            } else {
                $uploadMessage = 'Unable to save the uploaded file. Please try again.';
            }
        }
    } else {
        $uploadMessage = 'Please choose a Word document to attach.';
    }
}

include __DIR__ . '/../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Daily Documentary</h1>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row no-gutters align-items-stretch">
          <div class="col-12 col-lg-6 mb-2 pr-lg-2">
            <div class="card h-100" style="display: block; width: 100%; max-width: 100%; margin: 0; border-radius: 0.55rem; border: 1px solid #dce8f7; box-shadow: 0 0.15rem 0.45rem rgba(13, 110, 253, 0.08);">
              <div class="card-body p-2 p-sm-3" style="width: 100%;">
                <?php if (!empty($uploadMessage)): ?>
                  <div class="alert alert-info py-2 px-3 mb-3" id="uploadMessageAlert" role="alert"><?= htmlspecialchars($uploadMessage) ?></div>
                <?php endif; ?>

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                  <div class="font-weight-bold text-dark" style="font-size: 0.95rem;"><i class="fas fa-file-alt mr-1 text-primary"></i> Front Page</div>
                  <button class="btn btn-outline-primary shadow-sm" type="button" data-toggle="collapse" data-target="#frontPageBuilder" aria-expanded="false" aria-controls="frontPageBuilder" style="transition: all 0.2s ease-in-out; padding: 0.25rem 0.7rem; font-size: 0.9rem;">
                    <i class="fas fa-chevron-down mr-1" id="frontPageToggleIcon"></i> Create
                  </button>
                </div>

                <div class="collapse" id="frontPageBuilder">
                  <div class="border rounded p-1 shadow-sm" style="min-height: 72px; width: 100%; max-width: 100%; background: linear-gradient(135deg, #f9fbff 0%, #eef6ff 100%); transition: all 0.25s ease;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                      <div>
                        <div class="font-weight-bold text-dark" style="font-size: 0.9rem;"><i class="fas fa-file-alt mr-1 text-primary"></i> Front Page Document</div>
                        <div class="text-muted" style="font-size: 0.77rem;">Upload the front-page file in .doc or .docx format.</div>
                      </div>
                      <?php if (!empty($frontPageAttachmentName)): ?>
                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Attached</span>
                      <?php else: ?>
                        <span class="badge badge-light border text-muted"><i class="fas fa-paperclip mr-1"></i> Pending</span>
                      <?php endif; ?>
                    </div>

                    <?php if (!empty($frontPageAttachmentName)): ?>
                      <div class="alert alert-success py-1 px-2 mt-1 mb-1 small">
                        <i class="fas fa-file-word mr-1"></i>
                        <?= htmlspecialchars($frontPageAttachmentName) ?>
                      </div>
                    <?php else: ?>
                      <div class="small text-muted border rounded px-2 py-1 mt-1 bg-white">
                        <i class="fas fa-info-circle mr-1"></i> No file attached yet.
                      </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" class="mt-1">
                      <input type="file" class="d-none" id="frontPageDocument" name="front_page_doc" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>

                      <button type="button" class="btn btn-sm btn-outline-primary" id="frontPageUploadTrigger">
                        <i class="fas fa-<?= empty($frontPageAttachmentName) ? 'paperclip' : 'sync' ?> mr-1"></i>
                        <?= empty($frontPageAttachmentName) ? 'Attach File' : 'Replace File' ?>
                      </button>

                      <?php if (!empty($frontPageAttachmentName)): ?>
                        <button type="submit" class="btn btn-sm btn-outline-success ml-1" id="frontPageSubmitBtn" style="display:none;">
                          <i class="fas fa-check mr-1"></i> Confirm Upload
                        </button>
                      <?php endif; ?>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6 mb-2 pl-lg-2">
            <div class="card h-100" style="display: block; width: 100%; max-width: 100%; margin: 0; border-radius: 0.55rem; border: 1px solid #dce8f7; box-shadow: 0 0.15rem 0.45rem rgba(13, 110, 253, 0.08);">
              <div class="card-body p-2 p-sm-3" style="width: 100%;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                  <div class="font-weight-bold text-dark" style="font-size: 0.95rem;"><i class="fas fa-list-ul mr-1 text-primary"></i> Table of Contents</div>
                  <button class="btn btn-outline-primary shadow-sm" type="button" data-toggle="collapse" data-target="#tocBuilder" aria-expanded="false" aria-controls="tocBuilder" style="transition: all 0.2s ease-in-out; padding: 0.25rem 0.7rem; font-size: 0.9rem;">
                    <i class="fas fa-chevron-down mr-1" id="tocToggleIcon"></i> Create
                  </button>
                </div>

                <div class="collapse" id="tocBuilder">
                  <div class="border rounded p-1 shadow-sm" style="min-height: 72px; width: 100%; max-width: 100%; background: linear-gradient(135deg, #f9fbff 0%, #eef6ff 100%); transition: all 0.25s ease;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                      <div>
                        <div class="font-weight-bold text-dark" style="font-size: 0.9rem;"><i class="fas fa-list-ul mr-1 text-primary"></i> Table of Contents</div>
                        <div class="text-muted" style="font-size: 0.77rem;">Attach the document for the table of contents in .doc or .docx format.</div>
                      </div>
                      <span class="badge badge-light border text-muted"><i class="fas fa-paperclip mr-1"></i> Pending</span>
                    </div>

                    <div class="small text-muted border rounded px-2 py-1 mt-1 bg-white">
                      <i class="fas fa-info-circle mr-1"></i> No table of contents file attached yet.
                    </div>

                    <form method="post" enctype="multipart/form-data" class="mt-1">
                      <input type="file" class="d-none" id="tocDocument" name="toc_doc" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                      <button type="button" class="btn btn-sm btn-outline-primary" id="tocUploadTrigger">
                        <i class="fas fa-paperclip mr-1"></i> Attach File
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
  <?php include __DIR__ . '/../includes/script.php'; ?>
  <script>
    $(document).ready(function () {
      $('#frontPageBuilder').on('shown.bs.collapse', function () {
        $('#frontPageToggleIcon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
      });

      $('#frontPageBuilder').on('hidden.bs.collapse', function () {
        $('#frontPageToggleIcon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
      });

      $('#tocBuilder').on('shown.bs.collapse', function () {
        $('#tocToggleIcon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
      });

      $('#tocBuilder').on('hidden.bs.collapse', function () {
        $('#tocToggleIcon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
      });

      $('#frontPageUploadTrigger').on('click', function () {
        $('#frontPageDocument').trigger('click');
      });

      $('#tocUploadTrigger').on('click', function () {
        $('#tocDocument').trigger('click');
      });

      $('#frontPageDocument').on('change', function () {
        if (this.files && this.files.length > 0) {
          $('#frontPageSubmitBtn').show();
        }
      });

      if ($('#uploadMessageAlert').length) {
        setTimeout(function () {
          $('#uploadMessageAlert').fadeOut('slow');
        }, 5000);
      }
    });
  </script>
</div>
</body>
</html>