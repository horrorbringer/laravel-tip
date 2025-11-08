@extends('layouts.image-layout')
@section('title_content', 'Upload Image')

@section('content')
        <!-- Upload Form -->
            <div class="p-6">
                <form id="uploadForm" enctype="multipart/form-data" class="space-y-6">
                    <!-- Drag & Drop Area -->
                    <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors cursor-pointer">
                        <div id="dropContent">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Drop your image here</h3>
                            <p class="text-gray-500 mb-4">or click to browse files</p>
                            <input type="file" id="imageInput" name="image" accept="image/*" class="hidden">
                            <button type="button" onclick="document.getElementById('imageInput').click()"
                                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                Choose File
                            </button>
                        </div>

                        <!-- Preview Area -->
                        <div id="previewArea" class="hidden">
                            <img id="previewImage" class="max-w-xs max-h-48 mx-auto rounded-lg shadow-md mb-4">
                            <div class="flex items-center justify-center space-x-4">
                                <span id="fileName" class="text-sm text-gray-600"></span>
                                <button type="button" onclick="clearPreview()"
                                        class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Folder Selection -->
                    <div>
                        <label for="folder" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-folder mr-2"></i>Upload Folder
                        </label>
                        <select id="folder" name="folder" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="uploads">General Uploads</option>
                            <option value="profile">Profile Pictures</option>
                            <option value="products">Product Images</option>
                            <option value="gallery">Gallery</option>
                            <option value="documents">Documents</option>
                        </select>
                    </div>

                    <!-- Upload Button -->
                    <button type="submit" id="uploadBtn"
                            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="uploadBtnText">
                            <i class="fas fa-upload mr-2"></i>Upload Image
                        </span>
                        <span id="uploadingText" class="hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Uploading...
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Results Section -->
        <div id="resultsSection" class="hidden mt-8 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>Upload Results
            </h2>
            <div id="resultsContent"></div>
        </div>

        <!-- Recent Uploads -->
        <div id="recentUploads" class="mt-8 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-history mr-2"></i>Recent Uploads
            </h2>
            <div id="uploadsList" class="space-y-4">
                <p class="text-gray-500 text-center py-8">No recent uploads</p>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        // CSRF Token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Elements
        const dropZone = document.getElementById('dropZone');
        const imageInput = document.getElementById('imageInput');
        const uploadForm = document.getElementById('uploadForm');
        const previewArea = document.getElementById('previewArea');
        const dropContent = document.getElementById('dropContent');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');
        const uploadBtn = document.getElementById('uploadBtn');
        const uploadBtnText = document.getElementById('uploadBtnText');
        const uploadingText = document.getElementById('uploadingText');
        const resultsSection = document.getElementById('resultsSection');
        const resultsContent = document.getElementById('resultsContent');
        const uploadsList = document.getElementById('uploadsList');

        // Recent uploads storage
        let recentUploads = JSON.parse(localStorage.getItem('recentUploads') || '[]');

        // Drag and drop handlers
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');

            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                handleFileSelect(files[0]);
            } else {
                showToast('Please select a valid image file', 'error');
            }
        });

        // File input change handler
        imageInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        // Handle file selection
        function handleFileSelect(file) {
            if (file.size > 10 * 1024 * 1024) { // 10MB limit
                showToast('File size must be less than 10MB', 'error');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                fileName.textContent = `${file.name} (${formatFileSize(file.size)})`;
                dropContent.classList.add('hidden');
                previewArea.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        // Clear preview
        function clearPreview() {
            imageInput.value = '';
            dropContent.classList.remove('hidden');
            previewArea.classList.add('hidden');
            previewImage.src = '';
            fileName.textContent = '';
        }

        // Form submission
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!imageInput.files[0]) {
                showToast('Please select an image first', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('image', imageInput.files[0]);
            formData.append('folder', document.getElementById('folder').value);

            // Show loading state
            uploadBtn.disabled = true;
            uploadBtnText.classList.add('hidden');
            uploadingText.classList.remove('hidden');

            try {
                const response = await fetch('{{ route('images.uploadWithDatabase') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Image uploaded successfully!', 'success');
                    displayResults(result.data);
                    addToRecentUploads(result.data);
                    clearPreview();
                } else {
                    showToast(result.message || 'Upload failed', 'error');
                }
            } catch (error) {
                showToast('Network error. Please try again.', 'error');
                console.error('Upload error:', error);
            } finally {
                // Reset loading state
                uploadBtn.disabled = false;
                uploadBtnText.classList.remove('hidden');
                uploadingText.classList.add('hidden');
            }
        });

        // Display upload results
        function displayResults(data) {
            resultsContent.innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-green-800">Upload Successful!</h3>
                        <span class="text-sm text-green-600">${new Date().toLocaleString()}</span>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <img src="${data.url}" alt="Uploaded image" class="w-full max-h-48 object-cover rounded-lg shadow-md">
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="font-medium">URL:</span>
                                <button onclick="copyToClipboard('${data.url}')" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                            <input type="text" value="${data.url}" readonly class="w-full px-2 py-1 text-sm bg-gray-50 border rounded">

                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div><strong>Dimensions:</strong> ${data.width} × ${data.height}</div>
                                <div><strong>Format:</strong> ${data.format.toUpperCase()}</div>
                                <div><strong>Size:</strong> ${formatFileSize(data.size)}</div>
                                <div><strong>ID:</strong> ${data.public_id}</div>
                            </div>

                            <button onclick="deleteImage('${data.public_id}')" class="mt-2 bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            `;
            resultsSection.classList.remove('hidden');
            resultsSection.scrollIntoView({ behavior: 'smooth' });
        }

        // Add to recent uploads
        function addToRecentUploads(data) {
            const upload = {
                ...data,
                timestamp: new Date().toISOString()
            };

            recentUploads.unshift(upload);
            recentUploads = recentUploads.slice(0, 10); // Keep only 10 recent uploads

            localStorage.setItem('recentUploads', JSON.stringify(recentUploads));
            displayRecentUploads();
        }

        // Display recent uploads
        function displayRecentUploads() {
            if (recentUploads.length === 0) {
                uploadsList.innerHTML = '<p class="text-gray-500 text-center py-8">No recent uploads</p>';
                return;
            }

            uploadsList.innerHTML = recentUploads.map(upload => `
                <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <img src="${upload.url}" alt="Uploaded image" class="w-16 h-16 object-cover rounded-lg">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${upload.public_id}</p>
                        <p class="text-sm text-gray-500">${formatFileSize(upload.size)} • ${upload.width} × ${upload.height}</p>
                        <p class="text-xs text-gray-400">${new Date(upload.timestamp).toLocaleString()}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="copyToClipboard('${upload.url}')" class="text-blue-600 hover:text-blue-800 p-1">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button onclick="deleteImage('${upload.public_id}')" class="text-red-600 hover:text-red-800 p-1">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Delete image
        async function deleteImage(publicId) {
            if (!confirm('Are you sure you want to delete this image?')) return;

            try {
                const response = await fetch('/api/images/delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ public_id: publicId })
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Image deleted successfully', 'success');

                    // Remove from recent uploads
                    recentUploads = recentUploads.filter(upload => upload.public_id !== publicId);
                    localStorage.setItem('recentUploads', JSON.stringify(recentUploads));
                    displayRecentUploads();

                    // Hide results if it was the currently displayed image
                    if (resultsContent.innerHTML.includes(publicId)) {
                        resultsSection.classList.add('hidden');
                    }
                } else {
                    showToast('Failed to delete image', 'error');
                }
            } catch (error) {
                showToast('Network error', 'error');
            }
        }

        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('URL copied to clipboard!', 'success');
            });
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Show toast notification
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';

            toast.className = `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check' : type === 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle'} mr-2"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-3 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            document.getElementById('toastContainer').appendChild(toast);

            // Animate in
            setTimeout(() => toast.classList.remove('translate-x-full'), 100);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.classList.add('translate-x-full');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            displayRecentUploads();
        });
    </script>
@endsection
