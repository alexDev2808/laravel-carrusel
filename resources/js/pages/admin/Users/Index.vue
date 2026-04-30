<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import type { Role } from '@/types';

interface UserRow {
    id: number;
    name: string;
    email: string;
    role: Role;
    created_at: string;
}

defineProps<{
    users: UserRow[];
    roles: Role[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Usuarios', href: '/admin/users' },
        ],
    },
});

const page = usePage();
const authId = computed(() => (page.props.auth as any).user.id);

const roleVariant: Record<Role, 'default' | 'secondary' | 'destructive'> = {
    super:       'destructive',
    admin:       'default',
    colaborador: 'secondary',
    user:        'secondary',
};

const roleLabel: Record<Role, string> = {
    super:       'Super',
    admin:       'Admin',
    colaborador: 'Colaborador',
    user:        'Usuario',
};

function deleteUser(id: number) {
    if (!confirm('¿Eliminar este usuario?')) return;
    router.delete(`/admin/users/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Usuarios" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">Gestión de usuarios</h1>
            <Button as-child>
                <a href="/admin/users/create">Nuevo usuario</a>
            </Button>
        </div>

        <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Nombre</th>
                        <th class="px-4 py-3 text-left font-medium">Email</th>
                        <th class="px-4 py-3 text-left font-medium">Rol</th>
                        <th class="px-4 py-3 text-left font-medium">Registrado</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                    <tr v-for="user in users" :key="user.id" class="hover:bg-muted/30 transition-colors">
                        <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ user.email }}</td>
                        <td class="px-4 py-3">
                            <Badge :variant="roleVariant[user.role]">{{ roleLabel[user.role] }}</Badge>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ new Date(user.created_at).toLocaleDateString('es-MX') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <Button variant="outline" size="sm" as-child>
                                    <a :href="`/admin/users/${user.id}/edit`">Editar</a>
                                </Button>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    :disabled="user.id === authId"
                                    @click="deleteUser(user.id)"
                                >
                                    Eliminar
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="users.length === 0">
                        <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
