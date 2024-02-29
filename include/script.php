<script>
    document.addEventListener('DOMContentLoaded', function() {
        // เรียกฟังก์ชันเหล่านี้เพื่อโหลดข้อมูลเริ่มต้น
        updateDivisions();
        updateDepartments();
        updateSections();

        document.getElementById('divisionID').addEventListener('change', function() {
            // เมื่อมีการเลือก Division ใหม่ โหลดข้อมูล Department ใหม่
            updateDepartments(this.value);
            // รีเซ็ตข้อมูล Section หลังจาก Division ถูกเปลี่ยน
            updateSections();
        });

        document.getElementById('departmentID').addEventListener('change', function() {
            // เมื่อมีการเลือก Department ใหม่ โหลดข้อมูล Section ใหม่
            updateSections(this.value);
        });
    });

    function updateDivisions() {
        fetch('get_division.php')
            .then(response => response.json())
            .then(divisions => {
                var divisionSelect = document.getElementById('divisionID');
                divisionSelect.innerHTML = '<option value="">เลือกทั้งหมด</option>';
                divisions.forEach(function(division) {
                    divisionSelect.innerHTML += '<option value="' + division.division_id + '">' + division.name + '</option>';
                });
                $(divisionSelect).selectpicker('refresh');
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function updateDepartments(divisionId = '') {
        fetch('get_department.php' + (divisionId ? '?divisionId=' + divisionId : ''))
            .then(response => response.json())
            .then(departments => {
                var departmentSelect = document.getElementById('departmentID');
                departmentSelect.innerHTML = '<option value="">เลือกทั้งหมด</option>';
                departments.forEach(function(department) {
                    departmentSelect.innerHTML += '<option value="' + department.department_id + '">' + department.name + '</option>';
                });
                $(departmentSelect).selectpicker('refresh');
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function updateSections(departmentId = '') {
        fetch('get_section.php' + (departmentId ? '?departmentId=' + departmentId : ''))
            .then(response => response.json())
            .then(sections => {
                var sectionSelect = document.getElementById('sectionID');
                sectionSelect.innerHTML = '<option value="">เลือกทั้งหมด</option>';
                sections.forEach(function(section) {
                    sectionSelect.innerHTML += '<option value="' + section.section_id + '">' + section.name + '</option>';
                });
                $(sectionSelect).selectpicker('refresh');
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
</script>