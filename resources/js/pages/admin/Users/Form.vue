<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import InputError from '@/components/InputError.vue';
import type { Role } from '@/types';

interface UserData {
    id: number;
    name: string;
    email: string;
    role: Role;
}

const props = defineProps<{
    user?: UserData;
    roles: Role[];
}>();

const isEditing = computed(() => !!props.user);

const form = useForm({
    name:     props.user?.name ?? '',
    email:    props.user?.email ?? '',
    role:     props.user?.role ?? 'user',
    password: '',
});

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Usuarios', href: '/admin/users' },
            { title: 'Usuario', href: '#' },
        ],
    },
});

const roleLabel: Record<Role, string> = {
    super:        'Super',
    admin:        'Admin',
    colaborador:  'Colaborador',
    user:         'Usuario',
};

const page = usePage();
const errors = computed(() => (page.props.errors ?? {}) as Record<string, string>);

function submit() {
    if (isEditing.value) {
        form.patch(`/admin/users/${props.user!.id}`);
    } else {
        form.post('/admin/users');
    }
}
</script>

<template>
    <Head :title="isEditing ? 'Editar usuario' : 'Nuevo usuario'" />

    <div class="flex flex-col gap-6 p-4 max-w-lg">
        <h1 class="text-xl font-semibold">{{ isEditing ? 'Editar usuario' : 'Nuevo usuario' }}</h1>

        <form class="flex flex-col gap-4" @submit.prevent="submit">

            <div class="flex flex-col gap-1.5">
                <label class="text-sm text-muted-foreground">Nombre</label>
                <Input v-model="form.name" placeholder="Nombre completo" />
                <InputError :message="errors.name" />
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm text-muted-foreground">Email</label>
                <Input v-model="form.email" type="email" placeholder="correo@ejemplo.com" />
                <InputError :message="errors.email" />
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm text-muted-foreground">
                    Contraseña{{ isEditing ? ' (dejar vacío para no cambiar)' : '' }}
                </label>
                <Input v-model="form.password" type="password" placeholder="Mínimo 8 caracteres" />
                <InputError :message="errors.password" />
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm text-muted-foreground">Rol</label>
                <select
                    v-model="form.role"
                    class="border-input bg-transparent focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] focus-visible:ring-[3px]"
                >
                    <option v-for="r in roles" :key="r" :value="r">
                        {{ roleLabel[r] }}
                    </option>
                </select>
                <InputError :message="errors.role" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <Button type="submit" :disabled="form.processing">
                    {{ isEditing ? 'Guardar cambios' : 'Crear usuario' }}
                </Button>
                <Button variant="outline" as-child>
                    <a href="/admin/users">Cancelar</a>
                </Button>
            </div>

        </form>
    </div>
</template>
