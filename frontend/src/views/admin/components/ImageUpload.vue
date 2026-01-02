<template>
  <div>
    <!-- Upload Area -->
    <div class="relative">
      <!-- Preview -->
      <div
        v-if="preview"
        class="relative aspect-video bg-gray-900 rounded-lg overflow-hidden mb-4"
      >
        <img
          :src="preview"
          alt="Preview"
          class="w-full h-full object-cover"
        />
        <button
          type="button"
          @click="removeImage"
          class="absolute top-2 right-2 bg-red-600 hover:bg-red-500 text-white p-2 rounded-lg transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Upload Button -->
      <div
        v-else
        @click="triggerFileInput"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop"
        :class="[
          'border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-all',
          isDragging
            ? 'border-cyan-500 bg-cyan-900/20'
            : 'border-gray-700 hover:border-cyan-500 bg-gray-900/50'
        ]"
      >
        <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
        </svg>
        <p class="text-white font-medium mb-2">
          Kliknij lub przeciągnij zdjęcie
        </p>
        <p class="text-gray-400 text-sm">
          PNG, JPG, WEBP do 5MB
        </p>
      </div>

      <!-- Hidden File Input -->
      <input
        ref="fileInput"
        type="file"
        accept="image/*"
        @change="handleFileSelect"
        class="hidden"
      />
    </div>

    <!-- Upload Progress -->
    <div v-if="uploading" class="mt-4">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm text-gray-400">Przesyłanie...</span>
        <span class="text-sm text-cyan-400">{{ uploadProgress }}%</span>
      </div>
      <div class="w-full bg-gray-700 rounded-full h-2">
        <div
          class="bg-gradient-to-r from-cyan-500 to-purple-500 h-2 rounded-full transition-all"
          :style="{ width: uploadProgress + '%' }"
        ></div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="mt-4 bg-red-900/20 border border-red-500 rounded-lg p-3">
      <p class="text-red-400 text-sm">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  preview: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const isDragging = ref(false);
const uploading = ref(false);
const uploadProgress = ref(0);
const error = ref('');

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    validateAndUpload(file);
  }
};

const handleDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files[0];
  if (file) {
    validateAndUpload(file);
  }
};

const validateAndUpload = async (file) => {
  error.value = '';

  // Validate file type
  if (!file.type.startsWith('image/')) {
    error.value = 'Plik musi być obrazem (PNG, JPG, WEBP)';
    return;
  }

  // Validate file size (5MB)
  if (file.size > 5 * 1024 * 1024) {
    error.value = 'Rozmiar pliku nie może przekraczać 5MB';
    return;
  }

  // Upload file
  await uploadFile(file);
};

const uploadFile = async (file) => {
  uploading.value = true;
  uploadProgress.value = 0;

  try {
    const formData = new FormData();
    formData.append('image', file);

    const token = localStorage.getItem('authToken');
    
    const response = await axios.post(
      'http://localhost:8000/api/upload/image',
      formData,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
          uploadProgress.value = Math.round(
            (progressEvent.loaded * 100) / progressEvent.total
          );
        }
      }
    );

    // Emit the image URL
    const imageUrl = response.data.url || response.data.path;
    emit('update:modelValue', imageUrl);
  } catch (err) {
    console.error('Upload error:', err);
    error.value = 'Nie udało się przesłać obrazu: ' + (err.response?.data?.message || err.message);
  } finally {
    uploading.value = false;
    uploadProgress.value = 0;
  }
};

const removeImage = () => {
  emit('update:modelValue', '');
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

watch(() => props.modelValue, (newValue) => {
  if (!newValue && fileInput.value) {
    fileInput.value.value = '';
  }
});
</script>
