<?php if (!defined('_TASK_INCLUDED')) exit; ?>
<div class="section">
    <h2>Upload Result Sheet</h2>
    <p>CSV format: <code>stuid,subject,score</code></p>
    <form id="resultForm" enctype="multipart/form-data">
        <div class="form-group">
            <label>Term</label>
            <select name="term" required>
                <option>First Term</option>
                <option>Second Term</option>
                <option>Third Term</option>
            </select>
        </div>
        <div class="form-group">
            <label>Session</label>
            <input type="text" name="session" value="<?= date('Y') ?>" required>
        </div>
        <div class="form-group">
            <label>CSV File</label>
            <input type="file" name="csv" accept=".csv" required>
        </div>
        <input type="hidden" name="classid" value="<?= $_TASK['classid'] ?>">
        <button type="submit" class="btn">Upload Results</button>
    </form>
    <div id="resultMsg"></div>
</div>

<script>
document.getElementById('resultForm').onsubmit = function(e) {
    e.preventDefault();
    const form = new FormData(this);
    form.append('action', 'upload_result');
    fetch('teacher-action.php', { method: 'POST', body: form })
        .then(r => r.text())
        .then(msg => {
            document.getElementById('resultMsg').innerHTML = '<p class="success">' + msg + '</p>';
        });
};
</script>