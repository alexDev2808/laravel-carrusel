<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { echo } from '@laravel/echo-vue';
import { nextTick, onMounted, onUnmounted, ref } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Message {
    id: number;
    user_id: number;
    body: string;
    created_at: string;
    user: User;
}

const props = defineProps<{
    messages: Message[];
}>();

const page = usePage();
const authUser = page.props.auth.user as User;

const messages = ref<Message[]>([...props.messages]);
const messagesEnd = ref<HTMLDivElement | null>(null);

const form = useForm({ body: '' });

function scrollToBottom() {
    nextTick(() => {
        messagesEnd.value?.scrollIntoView({ behavior: 'smooth' });
    });
}

function send() {
    if (!form.body.trim()) return;
    form.post('/chat', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

onMounted(() => {
    scrollToBottom();
    echo().channel('chat').listen('MessageSent', (e: { message: Message }) => {
        messages.value.push(e.message);
        scrollToBottom();
    });
});

onUnmounted(() => {
    echo().leaveChannel('chat');
});

function formatTime(dateStr: string) {
    return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Chat" />

    <div class="flex h-[calc(100vh-4rem)] flex-col gap-0 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <!-- Header -->
        <div class="flex items-center gap-2 border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
            <div class="size-2 rounded-full bg-green-500" />
            <span class="text-sm font-medium">Chat general</span>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto px-4 py-4">
            <div class="flex flex-col gap-3">
                <div
                    v-for="msg in messages"
                    :key="msg.id"
                    class="flex gap-3"
                    :class="msg.user_id === authUser.id ? 'flex-row-reverse' : 'flex-row'"
                >
                    <!-- Avatar -->
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground">
                        {{ msg.user.name.charAt(0).toUpperCase() }}
                    </div>

                    <!-- Bubble -->
                    <div
                        class="max-w-[70%] rounded-2xl px-4 py-2 text-sm"
                        :class="msg.user_id === authUser.id
                            ? 'rounded-tr-sm bg-primary text-primary-foreground'
                            : 'rounded-tl-sm bg-muted text-foreground'"
                    >
                        <p v-if="msg.user_id !== authUser.id" class="mb-1 text-xs font-semibold opacity-70">
                            {{ msg.user.name }}
                        </p>
                        <p>{{ msg.body }}</p>
                        <p class="mt-1 text-right text-[10px] opacity-60">{{ formatTime(msg.created_at) }}</p>
                    </div>
                </div>
                <div ref="messagesEnd" />
            </div>
        </div>

        <!-- Input -->
        <div class="border-t border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
            <form class="flex gap-2" @submit.prevent="send">
                <Input
                    v-model="form.body"
                    placeholder="Escribe un mensaje..."
                    autocomplete="off"
                    class="flex-1"
                    @keydown.enter.prevent="send"
                />
                <Button type="submit" :disabled="form.processing || !form.body.trim()">
                    Enviar
                </Button>
            </form>
        </div>
    </div>
</template>
