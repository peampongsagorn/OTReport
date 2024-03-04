
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadModal" style="margin-right: 10px; margin-left:10px">
    Upload Excel Files
</button>

<!-- Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Excel Files</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="./importexceldata.php" method="post" enctype="multipart/form-data">
                    <!-- OT Record File Input Group -->
                    <div class="input-group mb-3">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="otRecordFile" name="excelFile" accept=".xlsx, .xls, .csv">
                            <label class="custom-file-label" for="otRecordFile">Choose OT Record File (.xlsx, .xls, .csv)</label>
                        </div>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('otRecordFile', 'Choose OT Record File (.xlsx, .xls, .csv)')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-x-fill" viewBox="0 0 16 16">
                                    <path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M6.854 7.146 8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 1 1 .708-.708" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <!-- OT Plan File Input Group -->
                    <div class="input-group mb-3">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="otPlanFile" name="planFile" accept=".xlsx, .xls, .csv">
                            <label class="custom-file-label" for="otPlanFile">Choose OT Plan File (.xlsx, .xls, .csv)</label>
                        </div>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('otPlanFile', 'Choose OT Plan File (.xlsx, .xls, .csv)')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-x-fill" viewBox="0 0 16 16">
                                    <path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M6.854 7.146 8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 1 1 .708-.708" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <!-- Employee File Input Group -->
                    <div class="input-group mb-3">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="employeeFile" name="employeeFile" accept=".xlsx, .xls, .csv">
                            <label class="custom-file-label" for="employeeFile">Choose Employee File (.xlsx, .xls, .csv)</label>
                        </div>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('employeeFile', 'Choose Employee File (.xlsx, .xls, .csv)')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-x-fill" viewBox="0 0 16 16">
                                    <path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M6.854 7.146 8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 1 1 .708-.708" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="save_excel_data">Upload Files</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function clearInput(inputId, defaultLabelText) {
        var input = document.getElementById(inputId);
        var label = input.nextElementSibling;
        input.value = '';
        label.textContent = defaultLabelText;
    }

    // Update the label of custom file input to show the file name
    document.querySelectorAll('.custom-file-input').forEach(inputElement => {
        inputElement.addEventListener('change', function(e) {
            let fileName = e.target.files.length > 0 ? e.target.files[0].name : '';
            let label = e.target.nextElementSibling;
            label.textContent = fileName ? fileName : 'Choose file';
        });
    });
</script>