// Pending Approvals Page Scripts

// Configure toastr notifications
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: '2800',
    extendedTimeOut: '1400'
};

function updateVisibleCount() {
  const visibleCards = document.querySelectorAll('#studentList .student-item:not([hidden])');
  const countNode = document.getElementById('visibleCount');
  if (countNode) {
    countNode.textContent = visibleCards.length;
  }
}

function filterStudents() {
  const query = document.getElementById('studentSearch').value.trim().toLowerCase();
  const cards = document.querySelectorAll('#studentList .student-item');

  cards.forEach(card => {
    const studentId = card.dataset.studentId.toLowerCase();
    const name = card.querySelector('.student-name').textContent.toLowerCase();
    const email = card.querySelector('.student-meta .meta-value:nth-of-type(2)').textContent.toLowerCase();
    const department = card.querySelector('.student-meta .meta-value:nth-of-type(3)').textContent.toLowerCase();
    const matches = !query || name.includes(query) || email.includes(query) || studentId.includes(query) || department.includes(query);
    card.hidden = !matches;
  });

  updateVisibleCount();
}

function setButtonState(button, busy, label) {
  button.disabled = busy;
  button.classList.toggle('btn-processing', busy);
  button.textContent = label;
}

async function approveStudent(studentId, button) {
  const confirmed = confirm('Approve registration for ' + studentId + ' now?');
  if (!confirmed) {
    return;
  }

  setButtonState(button, true, 'Approving...');
  const payload = new URLSearchParams({ student_id: studentId, action: 'approve' });

  try {
    const response = await fetch('process_registration_approval.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: payload.toString()
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(errorText || 'Server error');
    }

    const data = await response.json();

    if (data.success) {
      toastr.success('Approved ' + studentId + '.');
      setTimeout(() => location.reload(), 900);
    } else {
      toastr.error(data.message || 'Unable to approve this registration.');
      setButtonState(button, false, 'Approve');
    }
  } catch (error) {
    console.error('Approval error:', error);
    toastr.error(error.message || 'Cannot approve student right now.');
    setButtonState(button, false, 'Approve');
  }
}

async function rejectStudent(studentId, button) {
  const reason = prompt('Enter rejection reason (optional) for ' + studentId + ':', '');
  if (reason === null) {
    return;
  }

  setButtonState(button, true, 'Rejecting...');
  const payload = new URLSearchParams({ student_id: studentId, action: 'reject', reason });

  try {
    const response = await fetch('process_registration_approval.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: payload.toString()
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(errorText || 'Server error');
    }

    const data = await response.json();

    if (data.success) {
      toastr.warning('Rejected ' + studentId + '.');
      setTimeout(() => location.reload(), 900);
    } else {
      toastr.error(data.message || 'Unable to reject this registration.');
      setButtonState(button, false, 'Reject');
    }
  } catch (error) {
    console.error('Rejection error:', error);
    toastr.error(error.message || 'Cannot reject student right now.');
    setButtonState(button, false, 'Reject');
  }
}
