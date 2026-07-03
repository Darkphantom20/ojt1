document.querySelectorAll('.toggle-password').forEach(function(btn) {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    var wrapper = this.closest('.password-toggle-wrapper');
    if (!wrapper) return;
    var input = wrapper.querySelector('.password-toggle');
    if (!input) return;
    var icon = this.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  });
});
