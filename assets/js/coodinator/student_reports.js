// Student Reports Page Scripts

function filterStudents() {
  const dept = document.getElementById('departmentFilter').value;
  const studentSelect = document.getElementById('studentSelect');
  const options = studentSelect.querySelectorAll('option');
  
  options.forEach(option => {
    if (option.value === '') return;
    const optionDept = option.getAttribute('data-dept');
    option.style.display = (!dept || optionDept === dept) ? '' : 'none';
  });
}

$(document).ready(function() {
  $('.preloader').fadeOut();
});
