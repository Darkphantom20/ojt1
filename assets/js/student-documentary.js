document.addEventListener('DOMContentLoaded', function () {
  var toggleBtn = document.getElementById('toggle-images');
  var imageGallery = document.getElementById('image-gallery');
  var imageItems = document.querySelectorAll('.documentary-image');
  if (toggleBtn && imageGallery && imageItems.length > 0) {
    toggleBtn.addEventListener('click', function () {
      var isHidden = imageGallery.style.display === 'none';
      imageGallery.style.display = isHidden ? 'flex' : 'none';
      imageItems.forEach(function (item) {
        item.style.display = isHidden ? '' : 'none';
      });
      toggleBtn.textContent = isHidden ? 'Hide Images' : 'Show Images';
    });
  }
});

$(document).ready(function () {
  var config = window.studentDocumentaryConfig || {};

  $('.preloader').fadeOut();

  $('.server-time').each(function () {
    var timestamp = $(this).data('timestamp');
    if (timestamp) {
      var serverDate = new Date(timestamp);
      var localTime = serverDate.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
      $(this).text(localTime);
    }
  });

  $('.delete-entry-btn').on('click', function () {
    var entryId = $(this).data('id');
    if (confirm('Are you sure you want to delete this entry? This action cannot be undone.')) {
      var form = $('<form method="post">');
      form.append('<input type="hidden" name="delete_id" value="' + entryId + '">');
      form.append('<input type="hidden" name="entry_date" value="' + (config.selectedDate || '') + '">');
      $('body').append(form);
      form.submit();
    }
  });

  $('#open-daily-report-modal').on('click', function () {
    $('#dailyReportModal').modal('show');
    $('#dailyReportStep1').show();
    $('#dailyReportStep2').hide();
    $('#dailyReportNext').show();
    $('#dailyReportSubmit').hide();
    $('#dailyReportBack').hide();
    $('#report_images').val('');
    $('#report_note').val('');
    $('#reportImagesError').addClass('d-none').text('');
    $('#reportNoteError').addClass('d-none').text('');
  });

  function showReportStep(step) {
    $('#dailyReportStep1, #dailyReportStep2, #dailyReportStep3').hide();
    $('#dailyReportNext, #dailyReportPreview, #dailyReportSubmit').hide();
    $('#dailyReportBack').toggle(step !== 1);

    if (step === 1) {
      $('#dailyReportStep1').show();
      $('#dailyReportNext').show();
    } else if (step === 2) {
      $('#dailyReportStep2').show();
      $('#dailyReportPreview').show();
    } else {
      $('#dailyReportStep3').show();
      $('#dailyReportSubmit').show();
    }
  }

  var previewDate = config.selectedDate || config.today || '';
  var previewStudentId = config.studentId || '';
  var previewDepartment = config.studentDepartment || 'Not set';

  function escapeHtml(text) {
    return $('<div>').text(text).html();
  }

  function renderReportPreview() {
    var note = $('#report_note').val().trim();
    var fileInput = document.getElementById('report_images');
    var files = fileInput ? fileInput.files : [];
    var previewImages = $('#previewImages');

    $('#previewHeaderDate').text('Date: ' + previewDate);
    $('#previewHeaderStudent').text(' | Student ID: ' + previewStudentId);
    $('#previewHeaderDepartment').text(' | Department: ' + previewDepartment);

    previewImages.empty();
    if (files && files.length > 0) {
      Array.from(files).forEach(function (file) {
        if (!file.type.startsWith('image/')) {
          return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
          var img = $('<img>');
          img.attr('src', e.target.result);
          previewImages.append(img);
        };
        reader.readAsDataURL(file);
      });
    }

    var sanitized = note ? escapeHtml(note).replace(/\n/g, '<br>') : 'No report details entered.';
    $('#previewNote').html(sanitized);
  }

  $('#dailyReportNext').on('click', function () {
    var fileInput = document.getElementById('report_images');
    var files = fileInput ? fileInput.files : [];
    var fileCount = files ? files.length : 0;

    if (fileCount < 1 || fileCount > 3) {
      $('#reportImagesError').removeClass('d-none').text('Please select between 1 and 3 pictures.');
      return;
    }
    $('#reportImagesError').addClass('d-none').text('');
    showReportStep(2);
  });

  $('#dailyReportPreview').on('click', function () {
    var note = $('#report_note').val().trim();
    if (!note) {
      $('#reportNoteError').removeClass('d-none').text('Please enter the report details before previewing.');
      return;
    }
    $('#reportNoteError').addClass('d-none').text('');
    renderReportPreview();
    showReportStep(3);
  });

  $('#dailyReportBack').on('click', function () {
    var isPreviewVisible = $('#dailyReportStep3').is(':visible');
    if (isPreviewVisible) {
      showReportStep(2);
    } else {
      showReportStep(1);
    }
  });

  $('#dailyReportForm').on('submit', function () {
    var note = $('#report_note').val().trim();
    if (!note) {
      $('#reportNoteError').removeClass('d-none').text('Please enter the report details before submitting.');
      return false;
    }
    $('#reportNoteError').addClass('d-none').text('');
    return true;
  });
});
