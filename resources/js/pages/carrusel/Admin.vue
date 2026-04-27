<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface CarouselImage {
    id: number;
    url: string;
    title: string | null;
    order: number;
}

const props = defineProps<{
    images: CarouselImage[];
    duration: number;
    quote: string | null;
    author: string | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Carrusel', href: '/carrusel' },
            { title: 'Administrador', href: '/carrusel/admin' },
        ],
    },
});

// --- Subir imagen ---
const fileInput = ref<HTMLInputElement | null>(null);
const imageForm = useForm({ image: null as File | null, title: '' });

function onFileChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) imageForm.image = file;
}

function uploadImage() {
    imageForm.post('/carrusel/admin/images', {
        forceFormData: true,
        onSuccess: () => {
            imageForm.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
}

// --- Eliminar imagen ---
function deleteImage(id: number) {
    router.delete(`/carrusel/admin/images/${id}`, { preserveScroll: true });
}

// --- Reordenar ---
function moveUp(index: number) {
    if (index === 0) return;
    const ordered = [...props.images];
    [ordered[index - 1], ordered[index]] = [ordered[index], ordered[index - 1]];
    router.patch('/carrusel/admin/reorder', {
        order: ordered.map((img) => img.id),
    }, { preserveScroll: true });
}

function moveDown(index: number) {
    if (index === props.images.length - 1) return;
    const ordered = [...props.images];
    [ordered[index], ordered[index + 1]] = [ordered[index + 1], ordered[index]];
    router.patch('/carrusel/admin/reorder', {
        order: ordered.map((img) => img.id),
    }, { preserveScroll: true });
}

// --- Configuración (duración + frase) ---
const settingsForm = useForm({
    duration: props.duration,
    quote: props.quote ?? '',
    author: props.author ?? '',
});

function saveSettings() {
    settingsForm.patch('/carrusel/admin/settings', { preserveScroll: true });
}
</script>

<template>
    <Head title="Admin Carrusel" />

    <div class="flex flex-col gap-6 p-4">

        <!-- Configuración -->
        <section class="rounded-xl border border-sidebar-border/70 p-5 dark:border-sidebar-border">
            <h2 class="mb-4 text-base font-semibold">Configuración</h2>
            <form class="flex flex-col gap-4" @submit.prevent="saveSettings">

                <!-- Duración -->
                <div class="flex items-end gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm text-muted-foreground">Duración por imagen (1–60 s)</label>
                        <Input
                            v-model.number="settingsForm.duration"
                            type="number"
                            min="1"
                            max="60"
                            class="w-28"
                        />
                    </div>
                    <span class="mb-0.5 text-sm text-muted-foreground">
                        Actual: <strong>{{ duration }}s</strong>
                    </span>
                </div>

                <!-- Frase -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm text-muted-foreground">Frase inspiradora</label>
                    <textarea
                        v-model="settingsForm.quote"
                        rows="3"
                        maxlength="500"
                        placeholder="Escribe la frase de la semana..."
                        class="border-input bg-transparent placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] focus-visible:ring-[3px]"
                    />
                    <span class="text-right text-xs text-muted-foreground">{{ settingsForm.quote.length }}/500</span>
                </div>

                <!-- Autor -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm text-muted-foreground">Autor</label>
                    <Input
                        v-model="settingsForm.author"
                        placeholder="Ej. Albert Einstein"
                        class="max-w-xs"
                    />
                </div>

                <div>
                    <Button type="submit" :disabled="settingsForm.processing">
                        Guardar configuración
                    </Button>
                </div>
            </form>
        </section>

        <!-- Subir imagen -->
        <section class="rounded-xl border border-sidebar-border/70 p-5 dark:border-sidebar-border">
            <h2 class="mb-4 text-base font-semibold">Agregar imagen</h2>
            <form class="flex flex-wrap items-end gap-3" @submit.prevent="uploadImage">
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm text-muted-foreground">Archivo (JPG, PNG, WebP — máx 5 MB)</label>
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*"
                        class="text-sm text-foreground file:mr-3 file:rounded-md file:border-0 file:bg-primary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-primary-foreground"
                        @change="onFileChange"
                    />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm text-muted-foreground">Título (opcional)</label>
                    <Input v-model="imageForm.title" placeholder="Ej. Bienvenida" class="w-52" />
                </div>
                <Button type="submit" :disabled="imageForm.processing || !imageForm.image">
                    {{ imageForm.processing ? 'Subiendo...' : 'Subir imagen' }}
                </Button>
            </form>
            <p v-if="imageForm.errors.image" class="mt-2 text-sm text-destructive">
                {{ imageForm.errors.image }}
            </p>
        </section>

        <!-- Imágenes actuales -->
        <section class="rounded-xl border border-sidebar-border/70 p-5 dark:border-sidebar-border">
            <h2 class="mb-4 text-base font-semibold">
                Imágenes ({{ images.length }})
                <a href="/carrusel" target="_blank" class="ml-3 text-sm font-normal text-primary hover:underline">
                    Ver carrusel ↗
                </a>
            </h2>

            <div v-if="images.length === 0" class="py-8 text-center text-sm text-muted-foreground">
                No hay imágenes aún. Sube la primera arriba.
            </div>

            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="(img, index) in images"
                    :key="img.id"
                    class="group relative overflow-hidden rounded-lg border border-sidebar-border/70 bg-muted dark:border-sidebar-border"
                >
                    <!-- Imagen -->
                    <div class="aspect-video w-full overflow-hidden">
                        <img
                            :src="img.url"
                            :alt="img.title ?? ''"
                            class="h-full w-full object-cover transition group-hover:scale-105"
                        />
                    </div>

                    <!-- Info + acciones -->
                    <div class="flex items-center justify-between gap-2 px-3 py-2">
                        <span class="truncate text-sm text-muted-foreground">
                            {{ img.title || `Imagen ${index + 1}` }}
                        </span>

                        <div class="flex shrink-0 items-center gap-1">
                            <!-- Subir -->
                            <button
                                class="rounded p-1 text-muted-foreground transition hover:bg-accent hover:text-foreground disabled:opacity-30"
                                :disabled="index === 0"
                                title="Mover arriba"
                                @click="moveUp(index)"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            <!-- Bajar -->
                            <button
                                class="rounded p-1 text-muted-foreground transition hover:bg-accent hover:text-foreground disabled:opacity-30"
                                :disabled="index === images.length - 1"
                                title="Mover abajo"
                                @click="moveDown(index)"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <!-- Eliminar -->
                            <button
                                class="rounded p-1 text-muted-foreground transition hover:bg-destructive/10 hover:text-destructive"
                                title="Eliminar"
                                @click="deleteImage(img.id)"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</template>
