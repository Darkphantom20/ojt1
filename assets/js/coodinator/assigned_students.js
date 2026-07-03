$(document).ready(function() {
  
  if ($('#undeployAlertBox').length === 0) {
    $('.card-body .table-responsive').before('<div id="undeployAlertBox" class="mb-3"></div>');
  }

  $(document).on('click', '.undeploy-btn', function() {
    const studentId = $(this).data('student-id');
    const studentName = $(this).data('student-name') || 'student';
    if (!studentId) return;

    if (!confirm('Remove ' + studentName + ' from the assigned office?')) {
      return;
    }

    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Removing');

    $.ajax({
      url: 'save_office_assignment.php',
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'undeploy',
        student_id: studentId
      },
      success(data) {
        if (data && data.success) {
          sessionStorage.setItem('undeployAlert', JSON.stringify({type: 'success', msg: (data.message || 'Assignment removed')}));
          location.reload();
        } else {
          sessionStorage.setItem('undeployAlert', JSON.stringify({type: 'danger', msg: (data && data.message) ? data.message : 'Failed to remove assignment'}));
          location.reload();
        }
      },
      error() {
        sessionStorage.setItem('undeployAlert', JSON.stringify({type: 'danger', msg: 'Failed to remove assignment'}));
        location.reload();
      }
    });
  });

  
  const undeployAlert = sessionStorage.getItem('undeployAlert');
  if (undeployAlert) {
    try {
      const alertObj = JSON.parse(undeployAlert);
      if (alertObj && alertObj.type && alertObj.msg) {
        $('#undeployAlertBox').html('<div class="alert alert-' + (alertObj.type === 'success' ? 'success' : 'danger') + '" id="undeployAlertMsg">' + $('<div>').text(alertObj.msg).html() + '</div>');
        setTimeout(function() {
          $('#undeployAlertMsg').fadeOut(500, function() { $(this).remove(); });
          sessionStorage.removeItem('undeployAlert');
        }, 5000);
      }
    } catch (e) { sessionStorage.removeItem('undeployAlert'); }
  }
});
