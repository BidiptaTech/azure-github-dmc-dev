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

        dropzone.addEventListener('click', function() {
            fileInput.click();
        });
    }

    if (browseBtn) {
        browseBtn.addEventListener('click', function(e) {
            e.preventDefault();
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
        // Validate file type
        const allowedTypes = [
            'text/csv',
            'application/csv',
            'text/plain'
        ];
        
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid CSV file.');
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

    // Form submission with progress
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Show progress section
            if (progressSection) {
                progressSection.classList.remove('d-none');
            }
            uploadBtn.disabled = true;
            
            // Simulate progress (in real implementation, you'd use XMLHttpRequest for actual progress)
            let progress = 0;
            const progressInterval = setInterval(function() {
                progress += Math.random() * 15;
                if (progress >= 95) {
                    progress = 95;
                    clearInterval(progressInterval);
                }
                
                if (progressBar) {
                    progressBar.style.width = progress + '%';
                }
                if (progressText) {
                    progressText.textContent = Math.round(progress) + '%';
                }
            }, 200);
            
            // Submit form
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);
                if (progressBar) {
                    progressBar.style.width = '100%';
                }
                if (progressText) {
                    progressText.textContent = '100%';
                }
                
                setTimeout(() => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Upload failed: ' + (data.message || 'Unknown error'));
                        if (progressSection) {
                            progressSection.classList.add('d-none');
                        }
                        uploadBtn.disabled = false;
                    }
                }, 500);
            })
            .catch(error => {
                clearInterval(progressInterval);
                if (progressSection) {
                    progressSection.classList.add('d-none');
                }
                uploadBtn.disabled = false;
                
                // Fallback to regular form submission
                this.submit();
            });
        });
    }
}); 