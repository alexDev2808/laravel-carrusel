<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { echo } from '@laravel/echo-vue';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

interface CarouselImage {
    id: number;
    url: string;
    title: string | null;
}

interface CarouselPayload {
    images: CarouselImage[];
    duration: number;
    quote: string | null;
    author: string | null;
}

const props = defineProps<{
    images: CarouselImage[];
    duration: number;
    quote: string | null;
    author: string | null;
}>();

const images = ref<CarouselImage[]>([...props.images]);
const duration = ref(props.duration);
const quote = ref(props.quote);
const author = ref(props.author);
const current = ref(0);
const progress = ref(0);

let slideTimer: ReturnType<typeof setInterval> | null = null;
let progressTimer: ReturnType<typeof setInterval> | null = null;

function clearTimers() {
    if (slideTimer) clearInterval(slideTimer);
    if (progressTimer) clearInterval(progressTimer);
}

function startTimers() {
    clearTimers();

    if (images.value.length <= 1) return;

    const durationMs = duration.value * 1000;
    const tick = 50;

    progressTimer = setInterval(() => {
        progress.value = Math.min(progress.value + (tick / durationMs) * 100, 100);
    }, tick);

    slideTimer = setInterval(() => {
        current.value = (current.value + 1) % images.value.length;
        progress.value = 0;
    }, durationMs);
}

function goTo(index: number) {
    current.value = index;
    progress.value = 0;
    startTimers();
}

const currentImage = computed(() => images.value[current.value] ?? null);

function formatDate(date: Date): string {
    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    const diaSemana = dias[date.getDay()];
    const dia = date.getDate();
    const mes = meses[date.getMonth()];
    const año = date.getFullYear();
    
    return `${diaSemana}, ${dia} de ${mes} de ${año}`;
}

watch(duration, startTimers);

watch(
    () => images.value.length,
    (len, prev) => {
        if (current.value >= len) current.value = Math.max(0, len - 1);
        if (prev === 0 && len > 0) startTimers();
    },
);

onMounted(() => {
    startTimers();

    echo().channel('carousel').listen('CarouselUpdated', (e: CarouselPayload) => {
        images.value = e.images;
        duration.value = e.duration;
        quote.value = e.quote;
        author.value = e.author;
        progress.value = 0;
        startTimers();
    });
});

onUnmounted(() => {
    clearTimers();
    echo().leaveChannel('carousel');
});
</script>

<template>
    <Head title="Carrusel" />
    <div class="flex h-screen w-full relative overflow-hidden">
        <!-- Efecto decorativo de fondo (pantalla completa) -->
        <div class="absolute top-0 right-0 w-full h-full">
            <div class="absolute top-1/4 right-0 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-1/3 left-1/4 w-80 h-80 bg-slate-400/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
            <div class="absolute top-1/2 left-0 w-72 h-72 bg-cyan-400/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s"></div>
        </div>

        <!-- Banner -->
        <div class="w-3/12 h-screen bg-gradient-to-br from-blue-950 via-blue-900 to-slate-900 p-2 text-white flex flex-col justify-between relative overflow-hidden shadow-2xl">

            <!-- Logo Section -->
            <div class="flex items-center justify-center pt-4">
                <img src="/Taurus_slogan.png" alt="Taurus Slogan" class="w-28 drop-shadow-lg transform hover:scale-105 transition duration-300">
            </div>

            <!-- Quote Section -->
            <div class="flex-1 min-h-0 flex flex-col justify-center px-2 overflow-hidden">
                <div class="pb-2 border-b border-blue-400/30">
                    <h1 class="mb-2 text-lg text-center font-black tracking-tight leading-tight">
                        <span class="bg-gradient-to-r from-blue-200 via-blue-100 to-slate-200 bg-clip-text text-transparent">
                            Frase de la semana
                        </span>
                    </h1>
                </div>

                <p class="text-start my-2 text-xl italic font-light leading-relaxed text-blue-100 overflow-hidden">{{ quote || '—' }}</p>
                <p class="text-end text-sm font-semibold tracking-widest text-blue-300 pl-2">{{ author ? `— ${author}` : '' }}</p>
            </div>

            <!-- Información adicional -->
            <div class="mb-1 px-5 py-3 rounded-xl bg-white/5 backdrop-blur-sm border border-blue-400/20 hover:bg-white/10 transition duration-300">
                <div class="flex flex-col items-center mb-1">
                    <img src="/Info.png" alt="Info" class="w-4 h-4 drop-shadow-lg">
                    <h3 class="text-md font-bold tracking-wide text-blue-200">Información general</h3>
                </div>
                <img src="/QR.png" alt="QR Code" class="w-20 h-20 mx-auto rounded-lg shadow-lg border border-blue-400/30 pt-0 bg-white/10">
            </div>
             
            <!-- Fecha y Hora -->
            <div class="flex flex-col items-center justify-center text-xl font-semibold text-blue-100 tracking-wider pt-4 border-t border-blue-400/20">
                <span class="text-sm text-blue-300 mb-2">{{ formatDate(new Date()) }}</span>
                <span class="text-5xl font-bold text-white drop-shadow-lg">{{ new Date().toLocaleTimeString('es-ES', { hour12: false }) }}</span>
            </div>
        </div>

        <div class="relative flex h-screen w-9/12 items-center justify-center overflow-hidden bg-gradient-to-br from-blue-950 via-blue-900 to-slate-900">

                <!-- Slides -->
                <template v-if="images.length > 0">
                    <transition-group name="fade" tag="div" class="absolute inset-0">
                        <img
                            v-for="(img, i) in images"
                            v-show="i === current"
                            :key="img.id"
                            :src="img.url"
                            :alt="img.title ?? ''"
                            class="absolute inset-0 h-full w-full object-contain"
                        />
                    </transition-group>

                    <!-- Overlay gradiente -->
                    <!-- <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" /> -->

                    <!-- Título -->
                    <!-- <div v-if="currentImage?.title" class="absolute bottom-16 left-0 right-0 px-8 text-center">
                        <p class="text-2xl font-semibold text-white drop-shadow-lg">{{ currentImage.title }}</p>
                    </div> -->

                    <!-- Barra de progreso -->
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-white/20">
                        <div
                            class="h-full bg-white transition-none"
                            :style="{ width: `${progress}%` }"
                        />
                    </div>

                    <!-- Dots -->
                    <!-- <div v-if="images.length > 1" class="absolute bottom-4 left-0 right-0 flex justify-center gap-2">
                        <button
                            v-for="(img, i) in images"
                            :key="img.id"
                            class="size-2 rounded-full transition-all"
                            :class="i === current ? 'bg-white scale-125' : 'bg-white/40 hover:bg-white/70'"
                            @click="goTo(i)"
                        />
                    </div> -->

                    <!-- Flechas -->
                    <!-- <button
                        v-if="images.length > 1"
                        class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-black/30 p-2 text-white backdrop-blur-sm transition hover:bg-black/50"
                        @click="goTo((current - 1 + images.length) % images.length)"
                    >
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button
                        v-if="images.length > 1"
                        class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-black/30 p-2 text-white backdrop-blur-sm transition hover:bg-black/50"
                        @click="goTo((current + 1) % images.length)"
                    >
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button> -->
                </template>

                <!-- Sin imágenes -->
                <div v-else class="flex flex-col items-center gap-4 text-white/60">
                    <svg class="size-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-lg">No hay imágenes en el carrusel</p>
                    <Link href="/carrusel/admin" class="rounded-lg bg-white/10 px-4 py-2 text-sm hover:bg-white/20">
                        Ir al administrador
                    </Link>
                </div>
            

            <!-- Enlace admin -->
            <!-- <Link
                href="/carrusel/admin"
                class="absolute right-4 top-4 rounded-lg bg-black/30 px-3 py-1.5 text-xs text-white/70 backdrop-blur-sm transition hover:bg-black/50 hover:text-white"
            >
                Admin
            </Link> -->
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.8s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
