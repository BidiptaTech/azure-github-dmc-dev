document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const browseBtn = document.getElementById('browseBtn');
    const uploadBtn = document.getElementById('uploadBtn');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFile = document.getElementById('removeFile');
    const uploadForm = document.getElementById('uploadForm');
    const progressSection = document.getElementById('progressSection');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    // Drag and drop functionality
    if (dropzone) {
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropzone.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });

        dropzone.addEventListener('click', function(e) {
            // Only trigger file input if the click was not on the browse button
            if (e.target !== browseBtn && !browseBtn.contains(e.target)) {
                fileInput.click();
            }
        });
    }

    if (browseBtn) {
        browseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event bubbling to dropzone
            fileInput.click();
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                handleFile(this.files[0]);
            }
        });
    }

    if (removeFile) {
        removeFile.addEventListener('click', function() {
            fileInput.value = '';
            fileInfo.classList.add('d-none');
            uploadBtn.disabled = true;
        });
    }

    function handleFile(file) {
        // Validate file type - support Excel and CSV files
        const allowedTypes = [
            'text/csv',
            'application/csv',
            'text/plain',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        // Also check file extensions as backup
        const fileName = file.name.toLowerCase();
        const allowedExtensions = ['.csv', '.xlsx', '.xls'];
        const hasValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));
        
        if (!allowedTypes.includes(file.type) && !hasValidExtension) {
            alert('Please select a valid file (.csv, .xlsx, .xls).');
            return;
        }

        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB.');
            return;
        }

        // Additional validation for hotel files
        if (window.location.pathname.includes('hotels')) {
            validateHotelFormat(file);
        }

        // Display file info
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileInfo.classList.remove('d-none');
        uploadBtn.disabled = false;
    }

    function validateHotelFormat(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const content = e.target.result;
            const lines = content.split('\n');
            
            // Check for required sections
            const requiredSections = ['BASIC_INFO', 'ROOM_CATEGORIES', 'END_HOTEL'];
            const foundSections = [];
            
            for (let line of lines) {
                if (line.startsWith('SECTION,')) {
                    const section = line.split(',')[1];
                    if (section) foundSections.push(section.trim());
                }
            }
            
            const missingSections = requiredSections.filter(section => 
                !foundSections.includes(section) && section !== 'END_HOTEL'
            );
            
            // Check for END_HOTEL marker
            const hasEndMarker = lines.some(line => line.trim() === 'END_HOTEL');
            
            if (missingSections.length > 0 || !hasEndMarker) {
                console.warn('Hotel format validation:', {
                    missingSections,
                    hasEndMarker,
                    foundSections
                });
                // Don't block upload, just warn in console
            }
        };
        reader.readAsText(file);
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Track if form is already being submitted
    let isSubmitting = false;

    // Form submission with progress
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            // Prevent double submission
            if (isSubmitting || uploadBtn.disabled) {
                e.preventDefault();
                return false;
            }
            
            // Validate file is selected
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert('Please select a file to upload.');
                return false;
            }
            
            // Mark as submitting
            isSubmitting = true;
            
            // Show progress section and disable button
            if (progressSection) {
                progressSection.classList.remove('d-none');
            }
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="ri-loader-line me-1"></i>Uploading...';
            
            // Disable dropzone interactions to prevent changes during upload
            if (dropzone) {
                dropzone.style.pointerEvents = 'none';
                dropzone.style.opacity = '0.6';
            }
            if (browseBtn) {
                browseBtn.style.pointerEvents = 'none';
            }
            
            // Simulate progress for user feedback
            let progress = 0;
            const progressInterval = setInterval(function() {
                progress += Math.random() * 10;
                if (progress >= 90) {
                    progress = 90;
                    clearInterval(progressInterval);
                }
                
                if (progressBar) {
                    progressBar.style.width = progress + '%';
                }
                if (progressText) {
                    progressText.textContent = Math.round(progress) + '%';
                }
            }, 300);
            
            // Allow normal form submission (no AJAX)
            // The progress will complete when page reloads with results
            return true;
        });
    }
}); 