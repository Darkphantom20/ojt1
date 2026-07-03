document.addEventListener('DOMContentLoaded', function() {
  const fileInput = document.getElementById('userAvatar');
  const previewImage = document.getElementById('profilePreview');
  const placeholder = document.getElementById('profilePlaceholder');

  if (!fileInput || !previewImage || !placeholder) return;

  fileInput.addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
      alert('Please select a valid image file.');
      fileInput.value = '';
      return;
    }

    const url = URL.createObjectURL(file);
    previewImage.src = url;
    previewImage.style.display = 'inline-block';
    placeholder.style.display = 'none';

    previewImage.onload = function() {
      URL.revokeObjectURL(url);
    };
  });
});
