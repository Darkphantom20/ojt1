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
  var modal = $('#dailyReportModal');
  var form = $('#dailyReportForm');
  var nextBtn = $('#dailyReportNext');
  var backBtn = $('#dailyReportBack');
  var submitBtn = $('#dailyReportSubmit');
  var stepLabel = $('#dailyReportStepLabel');
  var progressBar = $('#dailyReportProgress');
  var openBtn = $('#open-daily-report-modal');

  if (!modal.length || !form.length || !nextBtn.length || !backBtn.length || !submitBtn.length || !stepLabel.length || !progressBar.length || !openBtn.length) {
    return;
  }

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
      var deleteForm = $('<form method="post">');
      deleteForm.append('<input type="hidden" name="delete_id" value="' + entryId + '">');
      deleteForm.append('<input type="hidden" name="entry_date" value="' + (config.selectedDate || '') + '">');
      $('body').append(deleteForm);
      deleteForm.submit();
    }
  });

  var currentStep = 1;
  var totalSteps = 8;

  function updateDailyReportWizard() {
    $('.wizard-step').hide();
    $('#dailyReportStep' + currentStep).show();
    stepLabel.text('Step ' + currentStep + ' of ' + totalSteps);
    progressBar.css('width', (currentStep / totalSteps * 100) + '%');
    backBtn.toggle(currentStep > 1);
    nextBtn.toggle(currentStep < totalSteps);
    submitBtn.toggle(currentStep === totalSteps);
  }

  function showWizardMessage(message) {
    $('#dailyReportValidationMessage').text(message).show();
  }

  function clearWizardMessage() {
    $('#dailyReportValidationMessage').text('').hide();
  }

  openBtn.on('click', function () {
    currentStep = 1;
    clearWizardMessage();
    updateDailyReportWizard();
    modal.modal('show');
  });

  backBtn.on('click', function () {
    if (currentStep > 1) {
      currentStep -= 1;
      clearWizardMessage();
      updateDailyReportWizard();
    }
  });

  nextBtn.on('click', function () {
    clearWizardMessage();
    if (currentStep < totalSteps) {
      currentStep += 1;
    }
    updateDailyReportWizard();
  });

  form.on('submit', function () {
    clearWizardMessage();
    return true;
  });
});
