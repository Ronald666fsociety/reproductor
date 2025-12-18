<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VideoPlayer from '@/Components/VideoPlayer.vue';
import { Upload, Film, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    videos: Array<{ name: string; url: string }>;
}>();

const uploadForm = useForm({
    video: null as File | null,
});

const progress = ref(0);
const isUploading = ref(false);

const handleFileUpload = (e: any) => {
    uploadForm.video = e.target.files[0];
};

declare const route: any;

const submitUpload = () => {
    if (!uploadForm.video) return;
    
    isUploading.value = true;
    uploadForm.post(route('video.upload'), {
        forceFormData: true,
        onProgress: (p: any) => {
            progress.value = Math.round((p.percentage));
        },
        onSuccess: () => {
            isUploading.value = false;
            progress.value = 0;
            uploadForm.reset();
        },
        onError: () => {
            isUploading.value = false;
        }
    });
};

const selectedVideo = ref(props.videos.length > 0 ? props.videos[0] : null);

const selectVideo = (video: any) => {
    selectedVideo.value = video;
};
</script>

<template>
    <Head title="Video Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                My Video Stream
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Left Column: Video Player -->
                    <div class="lg:col-span-2 space-y-6">
                        <div v-if="selectedVideo" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <VideoPlayer 
                                :key="selectedVideo.url" 
                                :src="selectedVideo.url" 
                                :title="selectedVideo.name" 
                            />
                            <div class="mt-4">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ selectedVideo.name }}</h3>
                                <p class="text-sm text-gray-500 mt-1">Uploaded and ready for streaming.</p>
                            </div>
                        </div>
                        
                        <div v-else class="bg-gray-100 dark:bg-gray-900 aspect-video rounded-lg flex flex-col items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-700">
                            <Film class="w-16 h-16 text-gray-400 mb-4" />
                            <p class="text-gray-500">No video selected. Upload your first video to start.</p>
                        </div>

                        <!-- Upload Section -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Upload New Video</h3>
                            <form @submit.prevent="submitUpload" class="space-y-4">
                                <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-8 text-center hover:border-blue-500 transition-colors">
                                    <input 
                                        type="file" 
                                        @change="handleFileUpload" 
                                        accept="video/*"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    />
                                    <div class="flex flex-col items-center">
                                        <Upload class="w-10 h-10 text-gray-400 mb-2" />
                                        <p class="text-sm text-gray-500">
                                            {{ uploadForm.video ? (uploadForm.video as any).name : 'Click or drag video to upload' }}
                                        </p>
                                    </div>
                                </div>

                                <div v-if="isUploading" class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" :style="{ width: progress + '%' }"></div>
                                    <p class="text-xs text-right mt-1 text-gray-500">{{ progress }}%</p>
                                </div>

                                <button 
                                    type="submit" 
                                    :disabled="uploadForm.processing || !uploadForm.video"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors disabled:opacity-50"
                                >
                                    {{ isUploading ? 'Uploading...' : 'Upload Video' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Video Library -->
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 h-fit max-h-[80vh] overflow-y-auto">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                <Film class="w-5 h-5 text-blue-500" />
                                Video Library
                            </h3>
                            
                            <div v-if="videos.length === 0" class="text-center py-8">
                                <p class="text-gray-500 text-sm">Library is empty.</p>
                            </div>

                            <div class="space-y-3">
                                <div 
                                    v-for="video in videos" 
                                    :key="video.url"
                                    @click="selectVideo(video)"
                                    class="p-3 rounded-lg border transition-all cursor-pointer group"
                                    :class="selectedVideo?.url === video.url ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700'"
                                >
                                    <div class="flex items-start gap-3">
                                        <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded">
                                            <Film class="w-4 h-4 text-gray-500" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ video.name }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">MP4 Format</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
