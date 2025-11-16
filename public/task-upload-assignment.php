<?php if (!defined('_TASK_INCLUDED')) exit; ?>
<div class="section">
    <h2>Upload Assignment</h2>
    <form id="assignForm" enctype="multipart/form-data">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="desc" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label>File (PDF, DOCX, etc.)</label>
            <input type="file" name="file" accept=".pdf,.doc,.docx,.txt" required>
        </div>
        <input type="hidden" name="classid" value="<?= $_TASK['classid'] ?>">
        <button type="submit" class="btn">Upload</button>
    </form>
    <div id="assignMsg"></div>
</div>

<script>
document.getElementById('assignForm').onsubmit = function(e) {
    e.preventDefault();
    const form = new FormData(this);
    form.append('action', 'upload_assignment');
    fetch('teacher-action.php', { method: 'POST', body: form })
        .then(r => r.text())
        .then(msg => {
            document.getElementById('assignMsg').innerHTML = '<p class="success">' + msg + '</p>';
            this.reset();
        });
};
</script>