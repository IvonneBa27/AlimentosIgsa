function toggleEdit(checkbox) {
    var row = checkbox.closest('tr');
    var textareas = row.querySelectorAll('textarea');
    textareas.forEach(function(textarea) {
        textarea.disabled = !checkbox.checked;
        if (checkbox.checked) {
            textarea.classList.remove('form-control-plaintext');
            textarea.classList.add('form-control');
        } else {
            textarea.classList.remove('form-control');
            textarea.classList.add('form-control-plaintext');
        }
    });
}